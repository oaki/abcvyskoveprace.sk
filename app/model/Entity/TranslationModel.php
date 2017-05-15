<?php
namespace App\Model\Entity;

use App\Model\BaseDbModel;
use Nette\Caching\Storages\FileStorage;

class TranslationModel extends BaseDbModel
{

    function __construct(\DibiConnection $connection, FileStorage $cache)
    {
        parent::__construct($connection, $cache);
        $this->id_name = '';
        $this->table   = 'lang_translate';
    }

    //@todo nejak to zmenit
    public function getIdLangFromIso($lang)
    {
        return $this->connection->select('id_lang')->from('lang')->where('iso = %s', $lang)->fetchSingle();
    }

    function fetchPairs($lang)
    {
        $key = 'fetchPairs( ' . $lang . ')';
        $r   = $this->loadCache($key);
        if ($r)
            return $r;

        return $this->saveCache($key, $this->getFluent('[key],[translate]')
            ->join('lang')->using('(id_lang)')
            ->where('iso=%s', $lang)
            ->fetchPairs('key', 'translate')
        );
    }
}
