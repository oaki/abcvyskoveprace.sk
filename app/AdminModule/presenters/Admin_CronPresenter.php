<?php
namespace App\AdminModule\Presenters;

use App\Presenters\BasePresenter;

/**
 * Class Admin_CronPresenter
 */
class  Admin_CronPresenter extends BasePresenter
{

    private $nehnutelnostiSkModel;

    function startup()
    {
        parent::startup();

        if (!$this->user->isAllowed('cron', 'edit'))
            throw new NAuthenticationException('Nedostatočne práva.');

        $this->nehnutelnostiSkModel = new NehnutelnostiSkModel($this->context->getService('dibi'));
    }

    function renderCheckPortalNehnutelnosti()
    {

        $realityModel = $this->context->getService('Reality');
        /**
         * @todo only active realities
         */
        $list = $realityModel->getFluent()
            ->removeClause('select')
            ->select('link_to_nehnutelnosti_sk, id_reality')
            ->where('link_to_nehnutelnosti_sk !=""')
            ->fetchAll();

        $prices = $this->nehnutelnostiSkModel->check($list);

        foreach ($prices as $obj) {
            $reality = $realityModel->getFluent()
                ->removeClause('select')
                ->select('price,id_reality,price_with_provision,nehnutelnosti_sk_decrease_price')
                ->where('id_reality=%i', $obj['id_reality'])
                ->fetch();

            if ($reality['price_with_provision'] > $obj['price']) {
                if ($obj['price'] <= $reality['price']) {
                    $this->log(
                        $obj['id_reality'],
                        'Cena je nizsia ako nakupna cena. Cenu sme neupravili.Nasa nakupna:' . $reality['price'] . ', nehnutelnosti_sk:' . $obj['price'],
                        array(
                            'link' => $obj['link']
                        ));
                } else {
                    $newPrice = $this->changePrice($obj['price'], $reality['price'], $reality['id_reality'], $reality['nehnutelnosti_sk_decrease_price']);
                    $this->log(
                        $obj['id_reality'],
                        'Cena bola upravena na ' . $newPrice . ',-EUR pri ID=' . $obj['id_reality'],
                        array(
                            'link' => $obj['link']
                        ));
                }
            } else {
                $this->log(
                    $obj['id_reality'],
                    'Nasa cena je lepsia. Cena:' . $reality['price_with_provision'] . ', nehnutelnosti_sk:' . $obj['price'],
                    array(
                        'link' => $obj['link']
                    ));
            }
        }

        $this->terminate();
    }

    private function changePrice($theirPrice, $oldPrice, $id_reality, $decrease = 100)
    {

        /**
         * @var model RealityModel
         */
        $model    = $this->context->getService('Reality');
        $newPrice = $theirPrice - $decrease;

//        $model->update(array('price_with_provision' => $newPrice), $id_reality);

//        120000 5%
        $provision         = $newPrice - $oldPrice;
        $percent_provision = 100 / ($newPrice / $provision);

        $r = $model->fetch($id_reality);

        $area = $r['floor_area'];
        switch ($r['type']) {

            case "2":
                $area = $r['ground_area'];
                break;

            case "4":
                $area = $r['gross_area'];
                break;

        };

        if ($area > 0) {
            $price_for_1m2_with_provision = $newPrice / $area;
        } else {
            $price_for_1m2_with_provision = 0;
        }

        $arr = array(
            'price_with_provision'         => $newPrice,
            'price_for_1m2_with_provision' => round($price_for_1m2_with_provision, 2),
            'provision'                    => round($provision, 2),
            'percent_provision'            => round($percent_provision, 2),
        );

        $model->updateWithParams($arr, $id_reality);

    }

    /**
     * @throws Exception
     * @throws NAbortException
     * @link http://www.realgroup.sk/admin/cron/checkdateonbazossk/?APPLICATION_API_KEY=Zxz3eARxmhPamV6iDwgsq%7DmPviMQupUPjRVjWeDVXEKF
     */
    function actionCheckDateOnBazosSk()
    {
        $db   = $this->context->getService('dibi');
        $list = $db->select('id_reality')
            ->from('reality')
            ->where('
                bazos_id !=""
                AND bazos_adddate < NOW() - INTERVAL 2 MONTH
                ')
            ->fetchAll();

        $realityModel = $this->context->getService('Reality');

        foreach ($list as $l) {
            $realityModel->update(array('bazos_id' => ''), $l['id_reality']);
        }

        $this->terminate();
    }

    private function log($id_reality, $msg, $data = array())
    {
        $db  = $this->context->getService('dibi');
        $arr = array(
            'id_reality' => $id_reality,
            'adddate'    => new DibiDateTime(),
            'data'       => print_r($data, true),
            'msg'        => $msg
        );

        $db->insert('log_nehnutelnosti_sk', $arr)->execute();
    }

}

