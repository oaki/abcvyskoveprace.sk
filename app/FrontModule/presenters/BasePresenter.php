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
     * @var Cart @inject
     */
    public $cart;

    /**
     * @var OrderSummary @inject
     */
    public $orderSummary;

    /**
     * @var ICartControlFactory @inject
     */
    public $cartControlFactory;

    /**
     * @var \App\Model\SimpleTranslator $translator
     */
    protected $translator;

    /**
     * @var \DibiConnection $connection
     */
    protected $connection;

    /**
     * @var \App\Model\Entity\Eshop\ProductModel $productModel
     */
    protected $productModel;

    /**
     * @var \App\Model\Entity\Eshop\ProductParamModel $productParamModel
     */
    protected $productParamModel;

    /**
     * @var \App\Model\Entity\Eshop\ProductMarkModel $productMarkModel
     */
    protected $productMarkModel;

    /**
     * @var \App\Model\Entity\Eshop\CategoryModel $categoryModel
     */
    protected $categoryModel;

    public function beforeRender()
    {
        $sessionStart = $this->getSession('start');
        if (isset($_GET['start']) AND $_GET['start'] == 1) {
            $sessionStart->verify = true;
        }

        if (isset($sessionStart->verify) AND $sessionStart->verify == true) {

        } else {
            header('Location: /uz-coskoro/');
            exit;
        }

        $this->template->nav = $this->getService('Page')->fetchAssoc(1, $this->lang);

        $this->template->nav['categories'] = $this->categoryModel->getCategoriesForMenu($this->id_lang);
        $this->template->nav['brands']     = $this->productModel->productMarkModel->getTopMarks(5);

        $this->template->nav['products'] = $this->productModel->getTopProductsToMenu($this->id_lang);

        $this->template->eshopDefaultLink = $this->link('Eshop:default', self::getResetSearchParams());
    }

    public static function getResetSearchParams()
    {
        return [
            'id_category' => null,
            'priceFrom'   => null,
            'priceTo'     => null,
            'stocks'      => null,
            'news'        => null,
            'sale'        => null,
            'top'         => null,
            'q'           => null,
        ];
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

    public function handleAddToCart($productId, $quantity = 1)
    {
        $this->addToCart($productId, $quantity);
        if ($this->isAjax()) {
            $this['cart']->redrawControl();
        } else {
            $this->redirect('this');
        }
    }

    private function addToCart($productId, $quantity)
    {
        $product = $this->productModel->getAllInfo($productId, $this->id_lang);
        $this->cart->addItem(
            $product->id_product_param,
            $quantity,
            $product->info->sale,
            $product->price
        )
            ->setName($product->info['name'])
            ->setImage($product->image)
            ->setData([
                'ean' => $product->EAN
            ])
            ->setLink('Product:default')
            ->setLinkArgs($product->id_product_param);
    }

    public function handleAddProductsToCart($products = [])
    {
        foreach ($products as $item) {
            $this->addToCart($item['productId'], $item['quantity']);
        }

        if ($this->isAjax()) {
            $this['cart']->redrawControl();
        } else {
            $this->redirect('this');
        }
    }

    public function injectProductModel(ProductModel $model)
    {
        $this->productModel = $model;
    }

    public function injectProductParamModel(ProductParamModel $model)
    {
        $this->productParamModel = $model;
    }

    public function injectCategoryModel(CategoryModel $model)
    {
        $this->categoryModel = $model;
    }

    public function injectProductMarkModel(ProductMarkModel $model)
    {
        $this->productMarkModel = $model;
    }

    public function handleProductSearchForm(Nette\Application\UI\Form $form)
    {
        $this->redirect('Eshop:default', ['q' => $form->getValues()['query']] + self::getResetSearchParams());
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

            case 'cart' :
                return $this->cartControlFactory->create();

            case 'UserRegister' :
                $c = new UserRegisterControl();
                $c->setUserModel($this->context->getService('User'));

                return $c;

            case 'productSearchForm' :
                $f = new Nette\Application\UI\Form($this, $name);
                $f->addText('query', 'Vyhľadávanie')
                    ->addRule(Nette\Application\UI\Form::FILLED, 'Zadajte výraz pre vyhľadávanie')
                    ->setAttribute('placeholder', 'Vyhľadávanie');
                $f->addSubmit('btn', 'Odoslať')
                    ->setAttribute('class', 'btn btn-primary btn-search pull-right');
                $f->onSubmit[] = $this->handleProductSearchForm;

                if (isset($this->q)) {
                    $f->setDefaults(['query' => $this->q]);
                }

                return $f;

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