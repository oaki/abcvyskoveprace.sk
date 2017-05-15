<?php
namespace App\Components\Backend\TreeView;

interface ITreeViewRenderer
{

    function render(TreeView $node);
}