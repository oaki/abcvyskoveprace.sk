<?php
namespace App\FrontModule\Presenters;

use App\Model\Entity\Eshop\ProductModel;
use Nette\Application\BadRequestException;

class ProductPresenter extends BasePresenter
{

    /** @persistent */
    public $id_product_param;

    /**
     * @param $id id_product_param
     *
     * @throws BadRequestException
     */
    public function renderDefault($id_product_param)
    {

        $this->template->l = $this->productModel->getAllInfo($id_product_param, $this->id_lang);

        if (!$this->template->l OR $this->template->l['price']->price < 0.01)
            throw new BadRequestException('Product neexistuje. ID:' . $id_product_param);

//        $this->template->info = $this->productModel->fetch($this->template->l['id_product'], $this->id_lang);

        /*$allCategories = $this->categoryModel->getAllLinks();

        $categories = $this->productModel->getCategories($id);

        $longestUrlCategory = '';
        $bestId = 0;
        foreach ($categories as $category) {
            $id = $category['CATEGORY_ID'];
            if (isset($allCategories[$id]) AND strlen($allCategories[$id]['LINK']) > strlen($longestUrlCategory)) {
                $longestUrlCategory = $allCategories[$id]['LINK'];
                $bestId = $id;
            }
        }

        $category = $allCategories[$bestId];
        $category['PARENTS'] = array_reverse($category['PARENTS']);

        $breadcrumbs = [];
        foreach ($category['PARENTS'] as $id) {
              $breadcrumbs[] = [
                  'name'=>$allCategories[$id]['NAME'],
                  'url'=>$this->link('Eshop:default', array('category'=>$id)),
              ];
        }*/

        $mainCategory = $this->productModel->getConnection()->select('id_category')->from('category_product')
            ->join('category')->using('(id_category)')
            ->where('id_product = %i', $this->template->l['id_product'])
            ->orderBy('depth DESC')
            ->limit(1)
            ->fetchSingle();

        $this->template->breadcrumbs = [];
        $this->template->breadcrumbs = $this->categoryModel->getBreadcrumbs($mainCategory, $this->id_lang);

        foreach ($this->template->breadcrumbs as $k => $l) {
            $this->template->breadcrumbs[$k]['url'] = $this->link(':Front:Eshop:default', ['id_category' => $l['id_category']]);
        }

        $list = $this->productModel->getAlternativeProduct($this->template->l['id_product']);;

        $this->template->alternativeProducts = [];
        foreach ($list as $l) {
            $this->template->alternativeProducts[] = $this->productModel->getAllInfo($l['id_product_param'], $this->id_lang);
        }

//        $this->template->alternativeProducts = $this->productModel->getAlternativeProduct($this->template->l['id_product']);

    }
}