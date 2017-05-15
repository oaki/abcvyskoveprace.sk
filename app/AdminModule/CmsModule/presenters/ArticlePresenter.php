<?php
namespace App\AdminModule\CmsModule\Presenters;

/**
 * ArticlePresenter presenter.
 *
 * @author     John Doe
 * @package    MyApplication
 */
use App\Components\Backend\File\FileControl;
use App\Model\Entity\IModuleSlug;
use Nette\Application\BadRequestException;
use Nette\Application\Responses\JsonResponse;
use Nette\Application\UI\Form;
use Nette\Utils\Strings;

/**
 * @property-read NodeModel $nodeModel
 */
class ArticlePresenter extends BasePresenter
{

    function actionAdd()
    {
        $this->setView('default');
    }

    function handleGetSlug($id, $id_menu_item)
    {
        $post = $this->request->getPost();

        if (!isset($post['slug']) OR $post['slug'] == '') {
            $post['slug'] = Strings::webalize($post['title']);
        }

        $nodeModel = $this->getService('Node');
        $node_list = $nodeModel->getListModuleInfo($nodeModel->getFluent('id_node')->where('status != "not_in_system" AND id_menu_item = %i', $id_menu_item, "AND id_node != %i", $id)->fetchAll());

        $existSlugedModule = false;
        foreach ($node_list as $l) {
            $module = $this->getService($l->node_info->service_name);

            //if has IModuleSlug interface
            if ($module instanceof IModuleSlug) {
                if ($post['slug'] == $module->idToSlug($l->id_node)) {
                    $existSlugedModule = true;
                    break;
                }
            }
        }

        if ($existSlugedModule) {
            $post['slug'] = $post['slug'] . '-' . $id;
        }

        $this->sendResponse(
            new JsonResponse(array('slug' => Strings::webalize($post['slug'])))
        );

        $this->terminate();
    }

    function renderDefault($id)
    {

        $value = $this->getService('Article')->fetch($id);

        if (!$value AND $id != '')
            throw new BadRequestException('Article does not exist.');

        if ($value)
            $this['form']->setDefaults($value);
    }

    function handleSave(Form $form)
    {

        $v = $form->getValues();

        $model = $this->getService('Article');

        $model->update($v, $this->id);

        //musi sa zavolat change status node
        $this->getService('Node')->putInToTheSystem($this->id);

        $this->flashMessage('Článok bol upravený.');

        if ($this->isAjax()) {
            $this->redrawControl('form');
            $this->redrawControl('flashmessage');
        } else {
            $this->redirect('this');
        }

    }

    function createComponentForm()
    {

        $f = new Form;

        $f->getElementPrototype()->class = 'ajax';

        $f->addGroup('Obsah');
        $f->addText('title', 'Nadpis')
            ->setAttribute('placeholder', 'Sem vložte nadpis')
            ->setAttribute('class', 'span9 article_title');

        $f->addTextarea('text', 'Text')
            ->setAttribute('class', 'span9 mceEditor');

        $f->addGroup('Meta data');

        $f->addText('slug', 'Url adresa')
            ->setAttribute('placeholder', 'Url článku')
            ->setAttribute('class', 'span9 article_slug');

        $f->addText('meta_title', 'Meta title')
            ->setAttribute('class', 'span9 article_meta_title');

        $f->addTextarea('meta_description', 'Meta description')
            ->setAttribute('class', 'span9');

        $f->addGroup('Design');
        $f->addTextarea('css', 'Css')
            ->setAttribute('class', 'span9');
        $f->addTextarea('js', 'Js')->setAttribute('class', 'span9');

        $f->addGroup('Nastavenia');
        $f->addCheckbox('comments_enabled', "Comments enabled", array(0 => 'No', 1 => 'Yes'));

        $f->addGroup('Btn');
        $f->addSubmit('btn', 'Uložiť zmeny')
            ->getControlPrototype()->class = 'btn btn-success';

        $f->onSuccess[] = array($this, 'handleSave');

        $f->getElementPrototype()->onsubmit('tinyMCE.triggerSave()');

        return $f;
    }

    function createComponentFile($name)
    {

        $f = new FileControl($this, $name);

        $f->setIdFileNode($this->getService('FileNode')->getIdFileNode($this->id, 'Article'));

        $f->addInput(array('type' => 'text', 'name' => 'name', 'css_class' => 'input-medium', 'placeholder' => 'Sem umiestnite popis'));

        $f->saveDefaultInputTemplate();

        return $f;
    }
}