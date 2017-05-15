<?php

namespace App\AdminModule\EshopModule\Components\ProductGrid;

use App\Model\Entity\LangModel;
use NiftyGrid\DibiFluentDataSource;
use NiftyGrid\Grid;

class ProductGrid extends Grid
{

    protected $productModel;

    protected $langModel;

    protected $id_category;

    protected $id_lang;

    public function __construct($productModel, $id_category, LangModel $langModel)
    {
        parent::__construct();
        $this->productModel = $productModel;
        $this->langModel    = $langModel;
        $this->id_category  = $id_category;
        $this->id_lang      = $this->langModel->getDefaultLang();
    }

    function handleDelete($id)
    {
        $this->productModel->delete($id);
        $this->getPresenter()->flashMessage('Produkt bol zmazaný');
        $this->redrawControl();
        $this->getPresenter()->redrawControl('flashmessage');
    }

    protected function configure($presenter)
    {
        //Vytvoříme si zdroj dat pro Grid
        //Při výběru dat vždy vybereme id
        $q = $this->productModel
            ->getFluent('product.id_product, product.group_code,product_lang.name,product.our_id,product.ean')
            ->join('product_lang')->using('(id_product)')
            ->leftJoin('category_product')->using('(id_product)')
            ->where('product_lang.id_lang = %i', $this->id_lang, '%if', $this->id_category != '', '
					AND category_product.id_category = %i', $this->id_category, '%end
					AND product.status != "not_in_system"')
            ->groupBy('product.id_product');

        $source = new DibiFluentDataSource($q, $primary_key_name = 'id_product');
        //Předáme zdroj
        $this->setDataSource($source);

        //pro vypnutí stránkování a zobrazení všech záznamů
        $this->paginate = true;
        //zruší řazení všech sloupců
//		$this->enableSorting = FALSE;
        //nastavení šířky celého gridu
//		$this->setWidth('1000px');
        //defaultní řazení
//		$this->setDefaultOrder("article.id DESC");
        //počet záznamů v rozbalovacím seznamu
        $this->setPerPageValues(array(10, 20, 50, 100));
        //vypnutí řazení na konkrétní sloupec
//		$this->addColumn('column', 'Column')
//			->setSortable(FALSE);

        $this->addColumn('our_id', 'ID', null, null);

        $this->addColumn('ean', 'EAN', '100px')
            ->setTextFilter();
        $this->addColumn('name', 'Názov', '400px')
            ->setTextFilter();

        $this->addButton("edit", "Editovať")
            ->setClass("btn")
            ->setLink(function ($row) use ($presenter) {
                return $presenter->link("Product:default", $row['id_product']);
            })
            ->setText(\Nette\Utils\Html::el('i class=icon-pencil'))
            ->setAjax(false);

        $self = $this;

        $this->addButton("delete", "Zmazať")
            ->setClass("btn btn-danger confirm-ajax")
            ->setText(\Nette\Utils\Html::el('i class="icon-trash icon-white"'))
            ->setLink(function ($row) use ($self) {
                return $self->link("delete!", $row['id_product']);
            });

    }
}