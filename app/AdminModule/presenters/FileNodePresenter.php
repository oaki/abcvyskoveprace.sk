<?php

/**
 * Admin_FileNodePresenter presenter.
 *
 * @author     John Doe
 * @package    MyApplication
 */
namespace App\AdminModule\Presenters;

use App\Model\Entity\File\FileModel;
use Nette\Application\Responses\JsonResponse;

class FileNodePresenter extends Admin_BasePresenter
{

    /**
     * @persistent
     * @name id_file_node
     */
    public $id_file_node;

    function actionAdd()
    {
        $this->setView('default');
    }

    function actionDeleteFile($id_file)
    {
        $conf = $this->context->parameters['webimages'];

        $file = $this->getService('File')->deleteFile($id_file, $conf['uploadDir'], $conf['tempDir']);

        $this->responseFlashMessage('Súbor bol zmazaný.');
    }

    function actionSaveOrder()
    {

        $post = $this->request->getPost();

        $order = $post['order'];

        if (is_array($order)) {

            $file = $this->getService('File');

            foreach ($order as $k => $o) {

                list($tmp, $id_file) = explode("id_file-", $o);

                $arr = array(
                    'sequence' => $k,
                );

                $file->update($arr, $id_file);

            }
        }

        $this->responseFlashMessage('Poradie bolo zmenené');

    }

    /*
     * @return json
     */
    function actionGetFiles($id_file_node)
    {

        $files = $this->getService('FileNode')->getFiles($id_file_node);

        $this->addInfoForMultiuploadedFiles($files);

//		dde($files);
        $this->sendResponse(
            new JsonResponse($files, "text/plain")
        );
    }

    function actionUpload($id_file_node)
    {

        $files = $this->getRequest()->getFiles();

        $conf = $this->context->parameters['webimages'];

        $uploaded_file = $this->getService('File')->upload($id_file_node, $files, $conf, $this->getService('FileNode'));

        $this->addInfoForMultiuploadedFiles($uploaded_file);

        $this->sendResponse(
            new JsonResponse($uploaded_file, "text/plain")
        );
    }

    function responseFlashMessage($msg, $type = 'info')
    {
        $arr = array(
            'snippets' => array(
                'snippet--flashmessage' => '<script>new Notification("' . $msg . '", "' . $type . '");</script>'
            )
        );

        $this->sendResponse(
            new JsonResponse($arr, "text/plain")
        );
    }

    private function addInfoForMultiuploadedFiles(array &$files)
    {
        foreach ($files as $k => $f) {
            $this->addInfoForMultiuploaded($files[$k]);
        }
    }

    /*
     * Pre zobrazenie multiuploadu doplni do pola suborov info o suboroch, prida obrazok a ...
     */

    private function addInfoForMultiuploaded(&$arr)
    {

        $arr['delete_url']  = $this->link('deleteFile', array('id_file' => $arr['id_file']));
        $arr['delete_type'] = "DELETE";

        $arr['thumbnail_url'] = FileModel::getURL($arr['src'], $arr['ext'], 150, 150, 6);
        $arr['url']           = FileModel::getURL($arr['src'], $arr['ext'], 150, 150, 6, 'original');

    }

    function renderDefault($id = null)
    {

    }

    function actionSave()
    {
        $post = $this->getRequest()->getPost();

        $this->getService('File')->update($post, $post['id_file']);

        $this->responseFlashMessage('Upravené');

    }

}