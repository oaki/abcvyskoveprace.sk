<?php

namespace App\FrontModule\Presenters;

use App\FrontModule\Components\BannerControl;
use Nette,
    App\Model;

/**
 * Homepage presenter.
 */
class HomepagePresenter extends BasePresenter
{

    public function renderDefault()
    {

        $this->template->newProduct = $this->productParamModel
            ->getFluentWithProduct('id_product_param')
            ->where('news = 1')
            ->fetchAll(0, 20);

        foreach ($this->template->newProduct as $k => $l) {
            $this->template->newProduct[$k] = $this->productModel->getAllInfo($l['id_product_param'], $this->id_lang);
        }

        $this->template->choosedProduct = $this->productParamModel
            ->getFluentWithProduct('id_product_param')
            ->where('home = 1 OR our_tip = 1')
            ->orderBy("RAND()")
            ->limit(8)
            ->fetchAll();

        foreach ($this->template->choosedProduct as $k => $l) {
            $this->template->choosedProduct[$k] = $this->productModel->getAllInfo($l['id_product_param'], $this->id_lang);
        }

        $this->template->brands = $this->context->getService('ProductMarkModel')
            ->getTopMarks(20);
    }

    function actionLogOut()
    {

        $this->user->logout(true);

        $this->flashMessage('Boli ste úspešne odhlásený');
        $this->redirect(':Front:Homepage:default');
    }

    protected function createComponent($name)
    {
        switch ($name) {
            case 'banner':
                $b = new BannerControl();
                $b->setBannerModel($this->context->getService('BannerModel'));

                return $b;
        }

        return parent::createComponent($name);
    }

}
