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
