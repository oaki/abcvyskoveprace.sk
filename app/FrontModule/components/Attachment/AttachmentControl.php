<?php

namespace App\FrontModule\Components;

use App\Components\BaseControl;

class AttachmentControl extends BaseControl
{

    public $disallow_files = array('jpg', 'gif', 'png');

    public $images_files = array('jpg', 'gif', 'png');

    public $icon_dir, $abs_icon_dir;

    public $dimensions = array(
        'thumb' => array(
            'width'  => 400,
            'height' => 250,
            'flag'   => 5
        ),
        'big'   => array(
            'width'  => 800,
            'height' => 600,
            'flag'   => 1
        )
    );

    function renderFiles(array $files)
    {
        $template = $this->template;

        foreach ($files as $k => $f) {
            if (in_array($f['ext'], $this->disallow_files))
                unset($files[$k]);
        }

        $template->files = $files;

        $template->setFile(dirname(__FILE__) . '/files.latte');

        $template->render();
    }

    function renderImages(array $files, $id = null, array $dimension = null)
    {
        $template     = $this->template;
        $template->id = $id;

        if ($dimension == null)
            $dimension = $this->dimensions;

        $template->dimension = $dimension;

        foreach ($files as $k => $f) {
            if (!in_array($f['ext'], $this->images_files))
                unset($files[$k]);
        }

        $template->files = $files;

        $template->setFile(dirname(__FILE__) . '/images.latte');

        $template->render();
    }

}