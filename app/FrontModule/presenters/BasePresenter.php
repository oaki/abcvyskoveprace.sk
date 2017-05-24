<?php

namespace App\FrontModule\Presenters;

use App\FrontModule\Components\ArticleControl;
use App\FrontModule\Components\AttachmentControl;
use App\FrontModule\Components\ContactControl;
use App\FrontModule\Components\LoginControl;
use App\FrontModule\Components\NewsletterControl;
use App\FrontModule\components\OrderSummaryControl\OrderSummary;
use App\FrontModule\Components\UserRegisterControl;
use App\Helper\FormatHelper;
use App\Model\Entity\Eshop\CategoryModel;
use App\Model\Entity\Eshop\ProductMarkModel;
use App\Model\Entity\Eshop\ProductModel;
use App\Model\Entity\Eshop\ProductParamModel;
use IPub\VisualPaginator\Components as VisualPaginator;
use Nette;
use Oaki\Cart\Cart;
use Oaki\Cart\ICartControlFactory;

/**
 * Base presenter for all application presenters.
 */
abstract class BasePresenter extends \App\Presenters\BasePresenter
{

    /**
     * @var \App\Model\SimpleTranslator $translator
     */
    protected $translator;

    /**
     * @var \DibiConnection $connection
     */
    protected $connection;

    public function beforeRender()
    {
        $this->template->nav = $this->getService('Page')->fetchAssoc(1, $this->lang);
    }

    public function createTemplate($class = null)
    {
        $template = parent::createTemplate($class);
        $template->setTranslator($this->translator);

        /**
         * @var FormatHelper $formatHelper
         */
        $formatHelper = $this->context->getService('FormatHelper');

        $template->addFilter('amount', function ($count) use ($formatHelper) {
            return $formatHelper->number($count, 0, '.', ' ');
        });

        $template->addFilter('currency3dec', function ($price) use ($formatHelper) {
            return $formatHelper->currency3dec($price);
        });

        return $template;
    }

    function actionLogOut()
    {
        $this->user->logout(true);

        $this->flashMessage('Boli ste úspešne odhlásený');
        $this->redirect(':Front:Sign:default');
    }

    public function flashMessage($message, $type = 'alert-success')
    {
        return parent::flashMessage($message, $type);
    }

    protected function createComponent($name)
    {

        switch ($name) {
            case 'login' :
                $l = new LoginControl();
                $l->redrawControl();

                return $l;
            case 'attachment' :
                $l = new AttachmentControl();

                return $l;

            case 'UserRegister' :
                $c = new UserRegisterControl();
                $c->setUserModel($this->context->getService('User'));

                return $c;

            case 'visualPaginator':
//                $control = new VisualPaginator\Control;
//                $control->setTemplateFile(dirname(__FILE__) . '/templates/paginator.latte');
//                $control->disableAjax();

                // Init visual paginator
                $control = new \VisualPaginator();

                return $control;

            case 'Contact':
                $contact = new ContactControl();

                return $contact;
                break;

            case 'Article':
                $contact = new ArticleControl();

                return $contact;
                break;
        }

        return parent::createComponent($name);
    }

}