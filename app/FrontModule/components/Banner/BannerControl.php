<?php
namespace App\FrontModule\Components;

use App\Components\BaseControl;
use App\Model\Entity\BannerModel;

class BannerControl extends BaseControl
{

    /**
     * @var BannerModel
     */
    private $bannerModel;

    private $list = null;

    function render()
    {

        $template = $this->template;

        $template->list = $this->getList();

        $template->setFile(dirname(__FILE__) . '/default.latte');
        $template->render();
    }

    public function getList()
    {
        if ($this->list === null) {
            return $this->list = $this->bannerModel->getActiveBanners();
        } else {
            return $this->list;
        }
    }

    function renderTreeCols()
    {

        $template = $this->template;

        $template->list = $this->getList();

        $template->setFile(dirname(__FILE__) . '/treeCols.latte');
        $template->render();
    }

    function renderOneCol()
    {

        $template = $this->template;

        $template->list = $this->getList();

        $template->setFile(dirname(__FILE__) . '/oneCol.latte');
        $template->render();
    }

    /**
     * @return mixed
     */
    public function getBannerModel()
    {
        return $this->bannerModel;
    }

    /**
     * @param mixed $bannerModel
     */
    public function setBannerModel(BannerModel $bannerModel)
    {
        $this->bannerModel = $bannerModel;
    }

}