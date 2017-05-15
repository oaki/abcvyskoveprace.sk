<?php

namespace App\Model\Entity;

use App\Model\BaseDbModel;

class LangModel extends BaseDbModel
{

    protected $table = 'lang';

    public function getDefaultLang()
    {
        /* @todo cache it */

        return $this->loadWithSave('getDefaultLang()', function () {
            return $this->getFluent("id_lang")->where("is_default = '1'")->fetchSingle();
        });
    }

    public function convertIsoToId($iso)
    {
        /* @todo cache it */
        return $this->loadWithSave('convertIsoToId(' . $iso . ')', function () use ($iso) {
            return $this->getFluent('id_lang')->where('iso = %s', $iso)->fetchSingle();
        });
    }

    public function getAll()
    {
        /* @todo cache it */
        return $this->getFluent()->fetchAll();
    }
}
