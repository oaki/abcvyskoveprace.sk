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

        $template->addFilter('imageWithSizes', function ($filename, $alt = '', $class = 'vc_single_image-img attachment-full') use ($generator) {
            $image_array = $generator->getFileNameAndExtension($filename);
            $image1024   = $generator->getThumbUrl('default', $image_array['src'], $image_array['ext'], 1024, 768, 5);
            $image300    = $generator->getThumbUrl('default', $image_array['src'], $image_array['ext'], 300, 225, 5);
            $image768    = $generator->getThumbUrl('default', $image_array['src'], $image_array['ext'], 768, 576, 5);

            return '<img src="' . $image1024 . '" srcset="' . $image1024 . ' 1024w, ' . $image300 . ' 300w,' . $image768 . ' 768w" class="' . $class . '" alt="' . $alt . '" sizes="(max-width: 1024px) 100vw, 1024px"/>';
        });


        $template->addFilter('imageWithSizesSmall', function ($filename, $alt = '', $class = 'vc_single_image-img attachment-full') use ($generator) {
            $image_array = $generator->getFileNameAndExtension($filename);
            $image1024   = $generator->getThumbUrl('default', $image_array['src'], $image_array['ext'], 360, 270, 5);
            $image300    = $generator->getThumbUrl('default', $image_array['src'], $image_array['ext'], 300, 225, 5);
            $image768    = $generator->getThumbUrl('default', $image_array['src'], $image_array['ext'], 768, 576, 5);

            return '<img src="' . $image1024 . '" srcset="' . $image1024 . ' 1024w, ' . $image300 . ' 300w,' . $image768 . ' 768w" class="' . $class . '" alt="' . $alt . '" sizes="(max-width: 1024px) 100vw, 1024px"/>';
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
