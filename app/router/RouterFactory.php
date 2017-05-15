<?php

namespace App;

use App\Model\Entity\Eshop\CategoryModel;
use App\Model\Entity\Eshop\ProductParamModel;
use Nette\Application\Routers\Route;
use Nette\Application\Routers\RouteList;

/**
 * Router factory.
 */
class RouterFactory
{

    /**
     * @var CategoryModel
     */
    public $categoryModel;

    public $productParamModel;

    public function __construct(CategoryModel $categoryModel, ProductParamModel $productParamModel)
    {
        $this->categoryModel     = $categoryModel;
        $this->productParamModel = $productParamModel;
    }

    /**
     * @return \Nette\Application\IRouter
     */
    public function createRouter()
    {
        $router = new RouteList();

        $categoryModel     = $this->categoryModel;
        $productParamModel = $this->productParamModel;

        Route::$styles['id_category'] = [
            Route::PATTERN    => '.*?',
            Route::FILTER_IN  => function ($url) use ($categoryModel) {
                $r = $categoryModel->slugToId($url, $id_lang = 1); //@todo nejak domysliet pre viac jazykov
                return $r;
            },
            Route::FILTER_OUT => function ($url) use ($categoryModel) {
                $r = $categoryModel->idToSlug($url, $id_lang = 1); //@todo nejak domysliet pre viac jazykov
                return $r;
            }
        ];

        Route::$styles['id_product_param'] = [
            Route::PATTERN    => '.*?',
            Route::FILTER_IN  => function ($url) use ($productParamModel) {
                $r = $productParamModel->slugToId($url, $id_lang = 1);

                return $r;
            },
            Route::FILTER_OUT => function ($url) use ($productParamModel) {
                $r = $productParamModel->idToSlug($url, $id_lang = 1); //@todo nejak domysliet pre viac jazykov
                return $r;
            }
        ];

        $router[] = new Route('clanok[/<action>]/<id>', 'Front:Article:default');
        $router[] = new Route('stranka[/<action>]/<id>', 'Front:Page:default');
        $router[] = new Route('kosik[/<action>]', 'Front:Cart:default');
        $router[] = new Route('profil[/<action>]', 'Front:Profile:default');

        $router[] = new Route('produkt/<id_product_param>.html', 'Front:Product:default');

        $router[] = new Route('eshop[/<id_category>/]', 'Front:Eshop:default');

        $router[] = new Route('[<lang=sk cs|sk|en|de>/]<module=Front>/<presenter>/<action>[/<id>]', 'Homepage:default');

        $router[] = new Route('/img/<fileName>', 'Front:Preview:default');

        return $router;
    }

}
