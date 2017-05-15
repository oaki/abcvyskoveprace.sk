<?php
namespace App\Model;

interface IEntity
{

    public function getConnection();

    function insertAndReturnLastId($values);

    function insert($values);

    function delete($id);

    function update($values, $id);

    function getFluent($select_collums = '*');

    function fetch($id);

}