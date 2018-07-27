<?php
require_once dirname(realpath(__FILE__)) . "/BaseDataObject.php";

class UserDO extends BaseDataObject
{
    private static $Table = array(
        "name" => "users",
        "structure" => array(
            "login" => "string",
            "password" => "string",
            "email" => "string",
            "capabilities" => "object"
        ), 
        "relationship" => array(
            "toOne" => array(
            ),
            "toMany" => array(
                "UserMetadata" => array(
                    "foreignKey" => "userId",
                    "key" => "id",
                    "dependent" => true,
                    "plural" => "UserMetadata"
                )
            )
        )
    );

    private $metadata = array();

    function __construct($dbConfig = null, $record = array(), $cascade = true)
    {
        parent::__construct(self::$Table, $dbConfig, $record, $cascade);
    }

    public function reset()
    {
        parent::reset();
        $this->metadata = array();
    }

    public function setMetadata($arg1, $arg2 = null)
    {
        $argsLength = func_num_args();
        if($argsLength == 2)
        {
            $this->metadata[$arg1] = $arg2;
        }
        else
        {
            foreach($arg1 as $key => $value)
            {
                $this->setMetadata($key, $value);
            }
        }
    }

    public function getMetadata($arg = null)
    {
        $result = null;
        $argsLength = func_num_args();
        if($argsLength == 1)
        {
            if(isset($this->metadata[$arg]))
                $result = $this->metadata[$arg];
        }
        else
        {
            $result = $this->metadata;
        }
        return $result;
    }

    public function deleteMetaData($name)
    {
        $this->dataObjects["UserMetadata"]->delete(array("userId" => $this->get("id"), "name" => $name));
        unset($this->metadata[$name]);
    }

    public function load($id = null, $selection = "*")
    {
        parent::load($id, $selection);
        $this->loadMetadata();
    }

    public function loadMetadata()
    {
        $this->metadata = array();
        $metadata = $this->loadRelatedUserMetadata();
        foreach($metadata as $item)
        {
            $this->metadata[$item["name"]] = $item["value"];
        }
    }

    public function save()
    {
        parent::save();
        foreach($this->metadata as $key => $value)
        {
            $this->dataObjects["UserMetadata"]->loadByUserId(array("value" => $this->get("id"),
                "unique" => true), "*", array("field" => "name", "value" => $key));
            if($this->dataObjects["UserMetadata"]->hasRecord())
                $this->dataObjects["UserMetadata"]->set("value", $value);
            else
                $this->dataObjects["UserMetadata"]->set(array("name" => $key, "value" => $value, "userId" => $this->get("id")));
            $this->metadataDO->save();
        }
    }

}
