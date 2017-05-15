<?php
namespace App\Model;

use App\Model\Entity\TranslationModel;
use Nette\Localization\ITranslator;
use Nette\Object;

class SimpleTranslator extends Object implements ITranslator
{

    /** @var string */
    public $lang;

    protected $translate;

    protected $model;

    function __construct(TranslationModel $model)
    {
        $this->model = $model;
    }

    public function setLang($lang = null)
    {
        $this->lang      = $lang;
        $this->translate = $this->model->fetchPairs($this->lang);

    }

    /**
     * Translates the given string.
     *
     * @param  string     translation string
     * @param  int        count (positive number)
     *
     * @return string
     */
    public function translate($message, $count = 1)
    {
        $key = md5($message);
        if (!isset($this->translate[$key])) {
            $this->model->insert([
                'id_lang'   => $this->model->getIdLangFromIso($this->lang),
                'key'       => $key,
                'translate' => $message,
            ]);
            $this->translate[$key] = $message;
        }

        return $this->translate[$key];
    }

}