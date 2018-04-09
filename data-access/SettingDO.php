<?php
require_once dirname(realpath(__FILE__)) . "/BaseDataObject.php";

class SettingDO extends BaseDataObject
{
    private static $Table = array(
        "name" => "settings",
        "structure" => array(
            "name" => "string",
            "value" => "string"
        ) 
    );

    function __construct($dbConfig = null, $record = array(), $cascade = true)
    {
        parent::__construct(self::$Table, $dbConfig, $record, $cascade = true);
    }
}
