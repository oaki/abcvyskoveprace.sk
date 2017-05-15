<?php
namespace App\AdminModule\SettingModule\Presenters;

use App\Model\Entity\Eshop\VatModel;
use Nette\Application\UI\Form;
use App\Components\Backend\DataGrid\DataGrid;

/**
 * Description of Admin_VatPresenter
 *
 * @author oaki
 */
class VatPresenter extends BasePresenter
{

    public $vatModel;

    public function renderDefault()
    {

    }

    function handleSave(Form $form)
    {
        $values = $form->getValues();
        VatModel::init()->insert($values);
        $this->flashMessage('Daň bola pridaná');
        $this->redirect('this');
    }

    public function createComponentVatGrid($name)
    {
        $presenter = $this;

        $grid = new DataGrid($this, $name);
        $grid->setPrimaryKey('id_vat');
        $fields = function ($container) {
            $container->addText('name', '');
            $container->addText('value', '');
            $container->addSelect('is_default', 'Predvolený', [0 => 'nie', 1 => 'áno']);
        };

        $grid->addInlineAdd()->onControlAdd[] = $fields;
        $grid->getInlineAdd()->onSubmit[]     = function ($values) use ($presenter) {
            $presenter->flashMessage("Hodnota bola pridaná", 'success');
            $presenter->vatModel->insert($values);
            $presenter->redrawControl('flashmessage');
            $presenter['vatGrid']->redrawControl();
        };

        $grid->addInlineEdit()
            ->onControlAdd[] = $fields;

        $grid->getInlineEdit()->onSetDefaults[] = function ($container, $item) {
            $container->setDefaults($item);
        };

        $grid->getInlineEdit()->onSubmit[] = function ($id, $values) use ($presenter) {
            $presenter->flashMessage("Hodnota bola pridaná", 'success');
            $presenter->vatModel->update($values, $id);
            $presenter->redrawControl('flashmessage');
        };

        $grid->setDataSource($this->vatModel->getFluent());

        $grid->addColumnText('name', 'Názov');
        $grid->addColumnNumber('value', 'Hodnota');

        $grid->addColumnText('is_default', 'Predvolený')
            ->setRenderer(function ($item) {
                return ($item->is_default === 1) ? 'áno' : 'nie';
            });

        $grid->addAction('delete', '', 'deleteVat!')
            ->setIcon('trash')
            ->setTitle('Zmazat')
            ->setClass('btn  btn-xs btn-danger ajax')
            ->setConfirm('Naozaj chcete vymazať %s?', 'name');

        return $grid;
    }

    public function handleDeleteVat($id_vat)
    {
        $this->vatModel->delete($id_vat);
        $this->flashMessage("Hodnota bola vymazaná", 'success');
        $this->redrawControl('flashmessage');
        $this['vatGrid']->redrawControl();
    }

    public function injectVatModel(VatModel $model)
    {
        $this->vatModel = $model;
    }

}