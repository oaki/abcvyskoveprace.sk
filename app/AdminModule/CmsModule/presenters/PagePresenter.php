<?php
namespace App\AdminModule\CmsModule\Presenters;

use App\Components\Backend\Form\TwitterBootstrapForm;
use Nette\Application\Responses\JsonResponse;
use Nette\Application\UI\Form;
use Nette\Utils\Html;
use Nette\Utils\Strings;

/**
 * Description of Admin_Cms_PagePresenter
 *
 * @author oaki
 */
class PagePresenter extends BasePresenter
{

    /** @persistent */
    public $back_link = null;

    function renderAddItem()
    {
        $page = $this->getService('Page');

        if (!isset($this->template->parent_link))
            $this->template->parent_link = $this->getParentUrl($this->id_menu_item);
    }

    function renderEditItem($id_menu_item)
    {

    }

    function handleGetParentLink($parent_id)
    {

        $fullurl = 'http://' . $_SERVER['HTTP_HOST'] . $this->getParentUrl($parent_id);
        $this->sendResponse(
            new JsonResponse(array('fullurl' => $fullurl), "text/plain")
        );
        $this->terminate();
    }

    //zisti ci uz nie je taky slug
    function check($parent_id, $slug, $id_menu_item)
    {

        //ak je $parent_id NULL
        if ($parent_id == '') {
            return !(bool)$this->getService('Page')
                ->getFluent('1')
                ->where('
					slug = %s', $slug, '
					AND parent_id IS NULL
					%if', $id_menu_item, "
						AND id_menu_item!=%i", $id_menu_item, "
					%end
				")->fetchSingle();
        }

        if ($parent_id == $id_menu_item)
            $id_menu_item = false;

        return !(bool)$this->getService('Page')
            ->getFluent('1')
            ->where('
					slug = %s', $slug, '
					AND parent_id = %i', $parent_id, "
					%if", $id_menu_item, "
						AND id_menu_item!=%i", $id_menu_item, "
					%end
				")->fetchSingle();
    }

    function handleGetSlug($parent_id, $slug, $actual_id_menu_item)
    {
        $slug = Strings::webalize($slug);

        $new_slug = $slug;
//		dde($new_slug);
        $counter = 1;

        while (!$this->check($parent_id, $new_slug, $actual_id_menu_item)) {
            $new_slug = $slug . '-' . $counter;
            $counter++;
            if ($counter > 10) {
                break;
            }
        }

        $this->sendResponse(
            new JsonResponse(array('slug' => $new_slug))
        );
        $this->terminate();
    }

    private function getParentUrl($parent_id)
    {
        $link = '';

        if ($parent_id)
            $link = $this->link(':Front:Page:default', array('id' => $parent_id));

//		dde($link);

        return $link;
    }

    function handleSaveSlug(Form $form)
    {
        $values = $form->getValues();
        dde($values);
    }

    function createComponentFormNewUrl($name)
    {
        $f = new Form;
        $f->addText('slug')
            ->setRequired('Url musí byť vyplnená.');
//		$f->addSubmit('btn','Uložiť');
        $f->onSuccess[] = array($this, 'handleSaveSlug');

        return $f;
    }

    function handleUpdate(Form $form)
    {
        $values = $form->getValues();
//		dde($values);

        $page = $this->getService('Page');

        //uprave linku
        /*
         * @todo
         * upravit linky - dorobit
         */

        if ($values['update_link']) {

        }

        unset($values['update_link']);
        if ($values['id_menu_item'] == null) {

            $id_menu_item = $page->insertAndReturnLastId($values);

            $this->flashMessage("Položka bola pridaná");

        } else {
            $page->update($values, $values['id_menu_item']);
            $this->flashMessage("Položka bola úspešne upravená.");
            $id_menu_item = $values['id_menu_item'];
        }

        $this->redirect('Homepage:default', array('id_menu_item' => $id_menu_item));

    }

    function createComponentPageBaseForm($name)
    {
        $f = new TwitterBootstrapForm($this, $name);

        $f->addText('name', 'Názov')
            ->setAttribute('placeholder', 'Sem vložte nadpis')
            ->setAttribute('class', 'page-name')
            ->setRequired('Názov musí byť vyplnený');

//		$grouped = $f->addContainer('urlcontainer');

        $f->addText('slug', 'URL')
            ->setOption('description', Html::el('span id=fullurl'))
            ->setRequired('URL musí byť vyplnené')
            ->setAttribute('class', 'page-slug');

        $f->addCheckbox('update_link', 'Automaticky zmeniť podľa nazvu');

        $f->addSelect('parent_id', "Rodič")
            ->setAttribute('class', 'page-parent_id');

        $f->addText('meta_title', 'Meta titulok');
        $f->addText('meta_description', 'Meta popis');
        $f->addText('meta_keywords', 'Meta kľúčové slová');

        $f->addSelect('status', 'Status', array('live' => 'Live', 'deactivate' => 'Deaktivované'));

        $f->addSelect('is_home', 'Je táto podstránka úvodná?', array('0' => 'nie', '1' => 'áno'));
        $f->addHidden('lang');
        $f->addHidden('id_menu');
        $f->addHidden('id_menu_item');

        $f->onSuccess[] = array($this, 'handleUpdate');

        $tree = $this->getService('Page')->getTreeUseInSelect($this->lang, $this->id_menu);

        $f['parent_id']->setItems($tree);

        $f->setDefaults(array('lang' => $this->lang, 'id_menu' => $this->id_menu));

        return $f;

    }

    function createComponentAddPageForm($name)
    {
        $f = $this->createComponentPageBaseForm($name);
        $f->addSubmit('btn', 'Pridať')
            ->setAttribute('class', 'btn btn-success');

        $f->setDefaults(array('parent_id' => $this->id_menu_item));

        return $f;
    }

    function createComponentEditPageForm($name)
    {
        $f = $this->createComponentPageBaseForm($name);
        $f->addSubmit('btn', 'Upraviť')
            ->setAttribute('class', 'btn btn-success');

        $f->setDefaults($this->getService('Page')->fetch($this->id_menu_item));

        return $f;
    }

}
