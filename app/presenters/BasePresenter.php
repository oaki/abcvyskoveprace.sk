<?php

namespace App\Presenters;

use App\AdminModule\AclModule\Models\Acl;
use App\Helper\FormatHelper;
use Nette,
    App\Model;
use Tracy\Debugger;

/**
 * BasePresenter
 *
 * @category  Wienerboerse
 * @package   App\Presenters
 * @author    Pavol Bincik <pavol.bincik@salesxp.com>
 * @copyright salesXp GmbH
 * @version   Release: @package_version@
 * @link      http://salesxp.com
 */
abstract class BasePresenter extends Nette\Application\UI\Presenter
{

    /**
     * @var \App\Model\SimpleTranslator $translator
     */
    protected $translator;

    /** @var boolean */
    private $debugMode = false;

    /** @persistent */
    public $lang = 'sk';

    public $id_lang;

    public $langModel;

    /**
     * @return void
     */
    protected function startup()
    {
        parent::startup();

        $this->debugMode = $this->context->parameters['debugMode'];

        $cache = $this->context->getService('cache');
        $acl   = $cache->load('acl');
        if ($acl === null) {
            $acl = $cache->save('acl', new Acl($this->context->getService('connection')));
        }

        $this->user->setAuthorizator($acl);

        $this->translator = $this->context->getService('translator');
        $this->translator->setLang($this->lang);

        $this->id_lang = $this->langModel->getDefaultLang();

        if (!$this->context->hasService('FormatHelper')) {
            $this->context->addService('FormatHelper', new FormatHelper($this->context->parameters['formats'], $this->lang));
        }

    }

    public function createTemplate($class = null)
    {

        $template = parent::createTemplate($class);

        /**
         * @var \App\Model\Image\Generator $generator ;
         */
        $generator = $this->context->getService('ImageGenerator');

        $template->addFilter('img', function ($image_array, $type, $width, $height, $flags = 0) use ($generator) {

            if (!is_array($image_array)) {
                $image_array = @$generator->getFileNameAndExtension($image_array);
            }

            return $generator->getThumbUrl($type, $image_array['src'], $image_array['ext'], $width, $height, $flags);
        });

        $template->addFilter('imageWithSizes', function ($filename, $alt = '', $class = 'vc_single_image-img attachment-full') use ($generator) {
            $image_array = $generator->getFileNameAndExtension($filename);
            $image1024   = $generator->getThumbUrl('default', $image_array['src'], $image_array['ext'], 1024, 768, 5);
            $image300    = $generator->getThumbUrl('default', $image_array['src'], $image_array['ext'], 300, 225, 5);
            $image768    = $generator->getThumbUrl('default', $image_array['src'], $image_array['ext'], 768, 576, 5);

            return '<img src="' . $image1024 . '" srcset="' . $image1024 . ' 1024w, ' . $image300 . ' 300w,' . $image768 . ' 768w" class="' . $class . '" alt="' . $alt . '" sizes="(max-width: 1024px) 100vw, 1024px"/>';
        });

        $template->addFilter('imageFilename', function ($arr) use ($generator) {
//            $width='1024',$height='768',$flag='5', $alt = '', $class = 'vc_single_image-img attachment-full'
            $defaults = [
                'width'  => 1024,
                'height' => 768,
                'flag'   => 5,
                'alt'    => '',
                'class'  => '',
            ];

            $imageParams = array_merge($defaults, $arr);

            $image_array = $generator->getFileNameAndExtension($imageParams['filename']);
            $image       = $generator->getThumbUrl('default', $image_array['src'], $image_array['ext'], $imageParams['width'], $imageParams['height'], $imageParams['flag']);

            return '<img src="' . $image . '" class="' . $imageParams['class'] . '" alt="' . $imageParams['class'] . '" />';
        });

        $formatHelper = $this->context->getService('FormatHelper');

        $template->addFilter('currency', function ($price) use ($formatHelper) {
            return $formatHelper->currency($price);
        });

        return $template;

    }

    /**
     * @return boolean
     */
    public function isDebugMode()
    {
        return $this->debugMode;
    }

    public function getService($name)
    {
        return $this->context->getService($name);
    }

    public function injectLangModel(Model\Entity\LangModel $langModel)
    {
        $this->langModel = $langModel;
    }

}
