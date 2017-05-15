<?php
namespace App\Model\Entity;

/*
 * Module has own url address
 */
interface IModuleSlug
{

    /**
     * Translate part url to id module.
     *
     * @param  string
     *
     * @return id_node
     */
    public function slugToId($slug);

    /**
     * Translate id to part url.
     *
     * @param  id
     *
     * @return string
     */
    public function idToSlug($id);
}
