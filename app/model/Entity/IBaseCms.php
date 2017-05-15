<?php
namespace App\Model\Entity;

interface IBaseCms
{

    /**
     * Adds module to system.
     *
     * @param  integer
     *
     * @return last_insert_id
     */
    function addModuleToCms($id_node);

    /**
     * fecth module info
     *
     * @param  $id_node
     *
     * @return array info
     */
    function fetch($id_node);

    /**
     * identify values witch will be showed in node template
     *
     * @param  $node_values
     *
     * @return array ( node_name, node_desc )
     */
    function identifyTitleAndDescriptionForNode($node_values);
}
