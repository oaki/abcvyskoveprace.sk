<?php
namespace App\Model\Entity\File;

use App\Model\BaseDbModel;
use Nette\Utils\Strings;

class FileModel extends BaseDbModel
{

    protected $table = 'file';

    protected $id_name = 'id_file';

    function insert($values)
    {

        if (!isset($values['sequence'])) {
            $values['sequence'] = $this->getMaxSequence($values['id_file_node']);
            if ($values['sequence'] == null)
                $values['sequence'] = 0;
        }

        parent::insert($values);

    }

    function upload($id_file_node,/*$this->getRequest()->getFiles()*/
                    $files, $conf, FileNodeModel $fileNode)
    {

        $info = array();

        if (!empty($files['files'])) {

            foreach ($files['files'] as $k => $f) {

                $fileinfo = pathinfo($f->getName());

                if ($fileinfo['filename'] == 'blob') {

                    $new_file_name = 'blob_' . time() . rand(0, 1000);
                    $fileinfo      = array(
                        'dirname'   => ".",
                        'basename'  => $new_file_name . ".jpg",
                        'extension' => "jpg",
                        'filename'  => $new_file_name
                    );
                }

                $fileinfo['extension'] = strtolower($fileinfo['extension']);

                $filename = self::getUniqueFilename($conf['uploadDir'], $fileinfo['filename'], $fileinfo['extension']);

                $f->move($conf['uploadDir'] . '/' . $filename);

                $fileinfo = pathinfo($filename);

                $arr = array(
                    'id_file_node' => $id_file_node,
                    'src'          => $fileinfo['filename'],
                    'ext'          => strtolower($fileinfo['extension']),
                    'name'         => $fileinfo['filename'],
                    'size'         => $f->getSize()
                );

                $id_file = $this->insertAndReturnLastId($arr);

                $info[$k] = array(
                    'name'    => $filename,
                    'size'    => $f->getSize(),
                    'type'    => $f->getContentType(),
                    'error'   => $f->getError(),
                    'id_file' => $id_file,
                    'src'     => $arr['src'],
                    'ext'     => $arr['ext']

                );

            }
        }

//		$this->addInfoForMultiuploadedFiles($info);
        $files = $fileNode->getFiles($id_file_node);

        //aby vratilo iba aktualny subor, nie vsetky

        $actual_file = array();
        foreach ($files as $k => $f) {
            if ($f['id_file'] == $id_file)
                $actual_file[] = $f;
        }

        return $actual_file;
    }

    function update($values, $id)
    {

        if (isset($values['params'])) {
            $values['params'] = serialize($values['params']);
        }

        parent::update($values, $id);
    }

    private function getMaxSequence($id_file_node)
    {
        return $this->getFluent('MAX(sequence)')->where('id_file_node = %i', $id_file_node)->fetchSingle();
    }

    function deleteFile($id, $dir, $temp_dir)
    {

        $file     = $this->fetch($id);
        $filename = $file['src'] . '.' . $file['ext'];

        //vymazanie z temp adresara
        $files = scandir($temp_dir);

        unset($files[0]);
        unset($files[1]);

        foreach ($files as $l) {

            @list($filename_temp, $hash) = @explode('|', $l, 2);

            if ($file['src'] == $filename_temp) {

                unlink($temp_dir . '/' . $l);
            }
        }

        @unlink($dir . '/' . $filename);

        parent::delete($id);
    }

    public static function getURL($src, $ext, $width, $height, $flags = 0, $mode = 'url')
    {
        global $container;

        $file_conf = $container->parameters['webimages'];
        $wwwDir    = $container->parameters['wwwDir'];
//        var_dump($file_conf);
//        exit;

        $crypt = self::getCrypt();

        switch ($ext) {
            //obrazky
            case 'jpg':
            case 'png':
            case 'gif':

                switch ($mode) {
                    default :
                        return $file_conf['tempUrl'] . '/' . $src . '|' . $crypt->encrypt($width . '|' . $height . '|' . $flags) . '.' . $ext;
                        break;

                    case 'original':
                        return $file_conf['uploadUrl'] . '/' . $src . '.' . $ext;
                        break;

                    case 'temp_dir':
                        return $file_conf['tempDir'] . '/' . $src . '|' . $crypt->encrypt($width . '|' . $height . '|' . $flags) . '.' . $ext;
                        break;
                }
                break;

            //subory pre ktore mam ikony
            case 'pdf':
            case 'doc':
            case 'docx':
            case 'xls':
                $new_src = 'icon_' . $ext;
                $new_ext = 'jpg';

                //over ci je tam taky subor
                if (!is_file($file_conf['uploadUrl'] . '/' . $src . '.' . $ext)) {
                    copy($file_conf['uploadDir'] . '/../attachment-icon/' . $new_src . '.' . $new_ext, $file_conf['uploadDir'] . '/' . $new_src . '.' . $new_ext);
                }
                switch ($mode) {
                    default :
                        return $file_conf['tempUrl'] . '/' . $new_src . '|' . $crypt->encrypt($width . '|' . $height . '|' . $flags) . '.' . $new_ext;
                        break;

                    case 'original':
                        return $file_conf['uploadUrl'] . '/' . $src . '.' . $ext;
                        break;

                    case 'temp_dir':
                        return $file_conf['tempDir'] . '/' . $new_src . '|' . $crypt->encrypt($width . '|' . $height . '|' . $flags) . '.' . $new_ext;
                        break;
                }
                break;

            //ostatne typy suborov
            default:
                $new_src = 'icon_not_defined';
                $new_ext = 'jpg';

                switch ($mode) {
                    default :
                        return $file_conf['tempUrl'] . '/' . $new_src . '|' . $crypt->encrypt($width . '|' . $height . '|' . $flags) . '.' . $new_ext;
                        break;

                    case 'original':
                        return $file_conf['uploadUrl'] . '/' . $src . '.' . $ext;
                        break;

                    case 'temp_dir':
                        return $file_conf['tempDir'] . '/' . $new_src . '|' . $crypt->encrypt($width . '|' . $height . '|' . $flags) . '.' . $new_ext;
                        break;
                }
                break;
        }

    }

    public static function getCrypt()
    {

        global $container;

        $file_conf = $container->parameters['webimages'];

        $crypt       = new \Crypt ();
        $crypt->Mode = \Crypt::MODE_HEX;
        $crypt->Key  = $file_conf['hash'];

        return $crypt;

    }

    public static function getUniqueFilename($dir, $name, $ext)
    {
        $number = "";

        while (file_exists($dir . '/' . Strings::webalize($name) . $number . "." . $ext)) {
            ++$number;
        }

        return Strings::webalize($name) . $number . '.' . $ext;
    }
}