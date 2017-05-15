<?php

namespace App\FrontModule\Presenters;

use Nette,
    App\Model;

/**
 * Article presenter.
 */
class ArticlePresenter extends BasePresenter
{

    public $articleModel;

    public function renderDefault($id)
    {

        $this->template->article = $this->articleModel->get($id);

    }

    protected function createComponent($name)
    {
//        switch ($name) {
//            case 'banner':
//                $b = new BannerControl();
//                $b->setBannerModel($this->context->getService('BannerModel'));
//                return $b;
//        }
        return parent::createComponent($name);
    }

    public function injectArticleModel(Model\Entity\ArticleModel $model)
    {
        $this->articleModel = $model;
    }
}
