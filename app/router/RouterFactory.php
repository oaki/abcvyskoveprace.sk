<?php

namespace App;

use App\Model\Entity\ArticleModel;
use App\Model\Entity\Eshop\CategoryModel;
use App\Model\Entity\Eshop\ProductParamModel;
use App\Model\Entity\PageModel;
use Nette\Application\Routers\Route;
use Nette\Application\Routers\RouteList;
use Tracy\Debugger;

/**
 * Router factory.
 */
class RouterFactory
{

    /**
     * @var PageModel
     */
    public $pageModel;

    /**
     * @var ArticleModel
     */
    public $articleModel;

    public $productParamModel;

    public function __construct(PageModel $pageModel, ArticleModel $articleModel)
    {
        $this->pageModel    = $pageModel;
        $this->articleModel = $articleModel;
    }

    /**
     * @return \Nette\Application\IRouter
     */
    public function createRouter()
    {
        $router = new RouteList();

        $pageModel    = $this->pageModel;
        $articleModel = $this->articleModel;

//        Route::$styles['id'] = [
//            Route::PATTERN    => '.*?',
//            Route::FILTER_IN  => function ($url) use ($pageModel) {
//                $r = $pageModel->slugToId($url);
//
////                return null;
//                return $r;
//            },
//            Route::FILTER_OUT => function ($url) use ($pageModel) {
//                $r = $pageModel->idToSlug($url);
////                return null;
//                return $r;
//            }
//        ];

        $router[] = new Route('<id>.html', [
            'module'    => 'Front',
            'presenter' => 'Article',
            'action'    => 'default',
            'id'        => array(
                Route::PATTERN    => '.*?',
                Route::FILTER_IN  => function ($url) use ($articleModel) {
                    return $r = $articleModel->slugToId($url);
                },
                Route::FILTER_OUT => function ($id) use ($articleModel) {
                    return $articleModel->idToSlug($id);
                }
            )
        ]);

        $router[] = new Route('<id>.html', [
            'module'    => 'Front',
            'presenter' => 'Page',
            'action'    => 'default',
            'id'        => array(
                Route::PATTERN    => '.*?',
                Route::FILTER_IN  => function ($url) use ($pageModel) {
                    return $pageModel->slugToId($url);;
                },
                Route::FILTER_OUT => function ($url) use ($pageModel) {
                    return $pageModel->idToSlug($url);
                }
            ),
        ]);

//        $router[] = new Route('stranka[/<action>]/<id>', 'Front:Page:default');
        $router[] = new Route('profil[/<action>]', 'Front:Profile:default');

        $router[] = new Route('/img/<fileName>', 'Front:Preview:default');

        $router[] = new Route('[<lang=sk cs|sk|en|de>/]<module=Front>/<presenter>/<action>[/<id>]', 'Homepage:default');

        return $router;
    }

}
