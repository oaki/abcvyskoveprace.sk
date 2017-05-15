<?php
namespace App\AdminModule\GalleryModule\Presenters;

use App\AdminModule\Presenters\AdminPresenter;
use App\Model\Entity\GalleryModel;
use Nette\Security\AuthenticationException;

abstract class Admin_Gallery_BasePresenter extends AdminPresenter
{

    /** @persistent */
    public $id;

    /**
     * @property-read GalleryModel $gallery
     */

    protected $gallery;

    function startup()
    {
        parent::startup();

        if (!$this->user->isAllowed('spravca_obsahu', 'edit'))
            throw new AuthenticationException('Nedostatočne práva.');

        $this->gallery = $this->context->getService('GalleryModel');
    }

}
