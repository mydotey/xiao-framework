<?php
require_once dirname(realpath(__FILE__)) . "/BaseDataObject.php";

class UserMetadataDO extends BaseDataObject
{
    private static $Table = array(
        "name" => "user_metadata",
        "structure" => array(
            "userId" => "int",
            "name" => "string",
            "value" => "string"
       ),
       "relationship" => array(
           "toOne" => array(
               "User" => array(
                   "foreignKey" => "userId",
                   "key" => "id",
                   "dependent" => false
               )
           ),
       )
    );

    function __construct($dbConfig = null, $record = array(), $cascade = true)
    {
        parent::__construct(self::$Table, $dbConfig, $record, $cascade);
    }
}
