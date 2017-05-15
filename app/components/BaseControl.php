<?php

namespace App\Components;

use IPub\VisualPaginator\TVisualPaginator;
use Nette\Application\UI\Control;

class BaseControl extends Control
{

    use TVisualPaginator;

    public function createTemplate($class = null)
    {
        $template = parent::createTemplate($class);
        /**
         * Translator
         */
        $template->setTranslator($this->getContext()->getService('translator'));

        $generator = $this->getContext()->getService('ImageGenerator');

        $template->addFilter('img', function ($image_array, $type, $width, $height, $flags = 0, $dir = 'dir') use ($generator) {
            if (!is_array($image_array) AND !is_object($image_array)) {
                $image_array = $generator->getFileNameAndExtension($image_array);
            }

            return $generator->getThumbUrl($type, $image_array['src'], $image_array['ext'], $width, $height, $flags, $dir);
        });

        $formatHelper = $this->getContext()->getService('FormatHelper');

        $template->addFilter('currency', function ($price) use ($formatHelper) {
            return $formatHelper->currency($price);
        });

        $template->addFilter('currency3dec', function ($price) use ($formatHelper) {
            return $formatHelper->currency3dec($price);
        });

        $template->addFilter('amount', function ($count) use ($formatHelper) {
            return $formatHelper->number($count, 0, '.', ' ');
        });

        return $template;
    }

    public function getContext()
    {
        return $this->getPresenter(true)->context;
    }

    public function getService($name)
    {
        return $this->getContext()->getService($name);
    }
}
