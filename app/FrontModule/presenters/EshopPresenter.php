<?php

namespace App\FrontModule\Presenters;

use App\FrontModule\Forms\BaseForm;
use App\Model;
use Nette\Application\UI\Form;

class EshopPresenter extends BasePresenter
{

    const FILTER_MAX_PRICE_LIMIT = 200;

    /** @persistent */
    public $displayMode = 'normal';

    public $displayModeOption = ['normal' => 'normal', 'list' => 'list'];

    /** @persistent */
    public $id_category = null;

    /** @persistent */
    public $stocks = [];

    /** @persistent */
    public $news;

    /** @persistent */
    public $sale;

    /** @persistent */
    public $top;

    /** @persistent */
    public $order = 'relevant';//'`product.our_tip` desc, `sale` desc, `news` desc, price';

    public $orderOptions = [
        'relevant' => 'podľa relevancie',

        'price'      => 'od najlacnejšich',
        'price DESC' => 'od najdrahších',
//        'EAN DESC'          => 'podľa kódu zostupne',
//        'EAN'               => 'podľa kódu vzostupne',
//        'stock DESC' => 'podľa skladu zostupne',
//        'stock'      => 'podľa skladu vzostupne',

        'product_lang.name' => 'abecedne podľa názvu',
//        'product_lang.name DESC'      => 'podľa názvu zostupne',
//        'BRAND.NAME'             => 'podľa značky vzostupne',
//        'BRAND.NAME DESC'        => 'podľa značky zostupne',
    ];

    /** @persistent */
    public $itemsPerPage = 24;

    public $itemsPerPageOptions = [12 => 12, 24 => 24, 48 => 48, 96 => 96];

    /** @persistent */
    public $id_product_marks = [];

    /**
     * @persistent
     * var string search query
     */
    public $q = '';

    public $queryToDb = '';

    /** @persistent */
    public $sizes = [];

    /** @persistent */
    public $priceFrom;

    /** @persistent */
    public $priceTo;

    /** @persistent */
    public $colors = [];

    /** @persistent */
    public $materials = [];

    private $cache;

    public function beforeRender()
    {

        parent::beforeRender();

        $categories                 = $this->categoryModel->getTree($this->id_lang);
        $this->template->categories = $categories;
//        $this->template->categoryParents = (isset($categories[$this->id_category])) ? $categories[$this->id_category]['id_parent'] : [];

        $this->template->marks = $this->getAllMarks();

        $this->template->breadcrumbs     = [];
        $this->template->categoryParents = [];

//        dump($this->id_category);
//        exit;
        $cat1 = $this->template->category = $this->categoryModel->get($this->id_category, $this->id_lang);

        $this->template->categoryParents[$this->id_category] = $cat1;
//        dump($cat1);
//        exit;
        if (isset($cat1['id_parent']) AND $cat1['id_parent'] != null) {
            $cat2                                                = $this->categoryModel->fetch($cat1['id_parent']);
            $this->template->categoryParents[$cat1['id_parent']] = $cat2;

            if ($cat2['id_parent'] != null) {
                $cat3                                                = $this->categoryModel->fetch($cat2['id_parent']);
                $this->template->categoryParents[$cat2['id_parent']] = $cat3;
            }
        }

//        dump($this->template->categoryParents);
//        exit;
        if (!isset($this->itemsPerPageOptions[$this->itemsPerPage])) {
            $this->itemsPerPage = current($this->itemsPerPageOptions);
        }

    }

    private function getAllMarks()
    {
        $q = $this->productMarkModel->getFluent();

        if ($this->id_category != null) {
            $q->join('product')->using('(id_product_mark)')
                ->join('category_product')->using('(id_product)');

            $q->where('id_category = %i', $this->id_category);
        }

        return $q->orderBy('name')->fetchAll();
    }

    public function renderDefault()
    {

        $this->template->categoryChildren = $this->categoryModel->getChildren($this->id_category, $this->id_lang);

        $this->template->isUsedFilter = false;
        if ($this->q OR $this->sale OR $this->top OR $this->news OR $this->id_product_marks) {
            $this->template->isUsedFilter = true;
        }
        /**
         * Filters
         */

        $query = $this->productParamModel
            ->getQuery($this->id_lang)
//            ->select('category.*')
            ->join('category_product')->on('product.id_product = category_product.id_product');

//            ->where('product_param.is_main = 1');

        $query->where('price > 0.01'); //poistka ze produkt nestoji 0 EUR
        /**
         * Brand
         */

        if (!empty($this->id_product_marks)) {
            $query->where('id_product_mark IN %l', $this->id_product_marks);
        }

        /**
         * Sale
         */
        if (!empty($this->sale) OR !empty($this->news) OR !empty($this->top)) {
            $toSql = '(';
            if ($this->sale == 1) {
                $toSql .= ' sale = 1 OR';
            }
            if ($this->news == 1) {
                $toSql .= ' news = 1 OR';
            }
            if ($this->top == 1) {
                $toSql .= ' top = 1 OR';
            }

            $toSql = substr($toSql, 0, -2);

            $toSql .= ')';
            $query->where($toSql);
        }

        /**
         * Stocks
         */
        if (!empty($this->stocks)) {
            $query->where(Model\Entity\ProductModel::getStockSql($this->stocks));
        }

        /**
         * Price
         */
//        $userRowPrice = 'PRICE_SELLING';
//        if ($this->user->isLoggedIn() AND $this->user->getIdentity()->getData()['PRODUCT_PRICE'] != '') {
//            $userRowPrice = $this->user->getIdentity()->getData()['PRODUCT_PRICE'];
//        }

        // @todo zistit vat koeficient s vat tabulky
        $vatCoef = 1.2;
        if ($this->priceFrom != '') {
            $query->where('price>=%f', $this->priceFrom / $vatCoef);
        }

        if ($this->priceTo != '' AND $this->priceTo < self::FILTER_MAX_PRICE_LIMIT) {
            $query->where('price<=%f', $this->priceTo / $vatCoef);
        }

        /**
         * Query
         */
        if ($this->q != '') {
            $query->where('(
            product_lang.name LIKE %~like~', self::getCaseEnding($this->q), '
            OR EAN LIKE %~like~', $this->q, ')');
        }

        /**
         * Order
         */
        if (isset($this->orderOptions[$this->order])) {

            $toSql = $this->order;

            if ($this->order == 'relevant') {
                $toSql = '`product.our_tip` desc, `sale` desc, `news` desc, price';
            }
            $query->orderBy($toSql);
        }

        if ($this->id_category != '') {
            $query->where('category_product.id_category = %i', $this->id_category);
        }

        $query->removeClause('select')->select('product_param.id_product_param');

        $query->groupBy('product_param.id_product_param');

        $countQuery = clone $query;

        $paginator               = $this['visualPaginator']->getPaginator();
        $paginator->itemsPerPage = $this->itemsPerPage;
        $paginator->itemCount    = $this->template->productCount = count($countQuery);

        $query->limit($paginator->offset . ',' . $paginator->itemsPerPage);

        $products = $query->fetchAll();
//        dump($products);
//        exit;
        foreach ($products as $k => $l) {
            $products[$k] = $this->productModel->getAllInfo($l['id_product_param'], $this->id_lang);
        }

        $this->template->products = $products;

    }

    // used in filter

    public static function getCaseEnding($query)
    {
        if (strlen($query) > 3) {
            $query = substr($query, 0, -1);
        }

        return $query;
    }

    public function getProductMarkName($id_product_mark)
    {
        return $this->productMarkModel->getFluent('name')->where('id_product_mark = %i', $id_product_mark)->fetchSingle();
    }

    public function processFilterForm(Form $form)
    {
        $values = (array)$form->getValues();
        $values += ['q' => null];
        if ($this->isAjax()) {
            $this->sale      = $values['sale'];
            $this->priceFrom = $values['priceFrom'];
            $this->priceTo   = $values['priceTo'];
            $this->redrawControl('productList');
        } else {
            $this->redirect('this', $values);
        }

    }

    public function getFilterUrl($filterName, $filterItem)
    {
        $values    = $this->{$filterName};
        $newValues = [];
        foreach ($values as $k => $v) {
            if ($v !== $filterItem) {
                $newValues[$k] = $v;
            }
        }

        return $this->link(":Front:Eshop:default", [$filterName => $newValues]);

    }

    protected function createComponentFilterForm($name)
    {

        $form = new BaseForm($this, $name);
        $form->addText('priceFrom')
            ->setType('number')
            ->setAttribute('min', 0)
            ->setAttribute('max', self::FILTER_MAX_PRICE_LIMIT);

        $form->addText('priceTo')
            ->setType('number')
            ->setAttribute('min', 0)
            ->setAttribute('max', self::FILTER_MAX_PRICE_LIMIT);

        $marks = $this->getAllMarks();

        $arr = array();

        foreach ($marks as $mark) {
            $arr[$mark->id_product_mark] = $mark->name;
        }

        asort($arr);
        $form->addCheckboxList('id_product_marks', 'Vyberte:', $arr)
            ->setAttribute('class', 'icr-input')
            ->checkAllowedValues = false;

        $callback = function ($field) {
            $name = $field['name'];

            return $this->{$name};
        };

//        /** @persistent */
//        public $news;
//
//        /** @persistent */
//        public $sale;
//
//        /** @persistent */
//        public $top;

        $form->addCheckbox('news', 'Novinky');
        $form->addCheckbox('sale', 'Zľavy');
        $form->addCheckbox('top', 'TOP produkty');

        $form->setDefaults([
            'priceFrom'        => $this->priceFrom,
            'priceTo'          => $this->priceTo,
            'id_product_marks' => $this->id_product_marks,
            'news'             => $this->news,
            'sale'             => $this->sale,
            'top'              => $this->top,
        ]);

        $form->onSuccess[] = $this->processFilterForm;

        return $form;
    }

}
