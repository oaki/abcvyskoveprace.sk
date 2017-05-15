<?php
namespace App\AdminModule\SettingModule\Presenters;

use Nette\Application\UI\Form;

/**
 * Description of Admin_Setting_GenerateFilePresenter
 *
 * @author oaki
 */
class GenerateFilePresenter extends BasePresenter
{

    function handleGenerateFile(Form $form)
    {
        $values = $form->getValues();

        //generate presenter
        $this->createCmsPresenterFile($values['name']);

        //generate model
        $this->createCmsModelFile($values['name']);

        //generate sql table
        $this->createSqlTable($values['name']);

        $this->flashMessage('Hotovo');
        $this->redirect('this');
    }

    function createComponentForm($name)
    {
        $f = new Form;
        $f->addText('name', 'Nazov modulu')
            ->addRule(Form::FILLED, 'Nazov treba vyplanit');

        $f->addSelect('type', 'Čo generovať', array(0 => 'Nový CMS modul'))
            ->addRule(Form::FILLED, 'Treba vyplanit');

        $f->addSubmit('btn', 'Generovat');

        $f->onSuccess[] = array($this, 'handleGenerateFile');

        return $f;
    }

    public function renderDefault()
    {

    }

    private function createCmsPresenterFile($name)
    {
        $template = clone($this->template);
        $template->setFile(APP_DIR . '/AdminModule/SettingModule/templates/GenerateFile/class_templates/Admin_Cms_ClassPresenter.latte');
        $template->name = $name;

        $path = APP_DIR . '/AdminModule/CmsModule/presenters/Admin_Cms_' . $name . 'Presenter.php';
//		echo $path;exit;
        $this->tryWriteFile($path, (string)$template);

        //sablona
        $template = clone($this->template);
        $template->setFile(APP_DIR . '/AdminModule/SettingModule/templates/GenerateFile/class_templates/Admin_Cms_DefaultTemplate.latte');
        $template->name = $name;
        $path           = APP_DIR . '/AdminModule/CmsModule/templates/' . $name . '/default.latte';

//		echo $path;exit;
        $this->tryWriteFile($path, (string)$template);
    }

    private function createCmsModelFile($name)
    {
        $template = clone($this->template);
        $template->setFile(APP_DIR . '/AdminModule/SettingModule/templates/GenerateFile/class_templates/Admin_Cms_DefaultTemplate.latte');
        $template->name = $name;

        $path = APP_DIR . '/models/CMS/' . $name . 'Model.php';
        $this->tryWriteFile($path, (string)$template);
    }

    private function createSqlTable($name)
    {

        $name = NStrings::lower($name);
        $sql  = '
CREATE TABLE IF NOT EXISTS `' . $name . '` (
  `id_node` int(11) unsigned NOT NULL,
  `add_date` datetime NOT NULL,
  `title` text NOT NULL,
  `text` text NOT NULL,
  PRIMARY KEY (`id_node`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
';

        $this->getService('dibi')
            ->query($sql);
    }

    protected function tryWriteFile($path, $content)
    {
        if (!file_exists($path)) {
            $dir = pathinfo($path, PATHINFO_DIRNAME);

            if (!file_exists($dir)) {
                mkdir($dir);
                $this->flashMessage("Directory $dir created.");
            }

            file_put_contents($path, (string)$content);
            $this->flashMessage("File $path created.");
        }
    }
}