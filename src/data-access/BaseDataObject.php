<?php
require_once dirname(realpath(__FILE__)) . "/../libs/CommonTools.php";
require_once dirname(realpath(__FILE__)) . "/../libs/EasySQL.php";

abstract class BaseDataObject
{
    protected static function lcfirst($string)
    {
        $result = "";
        $length = strlen($string);
        if($length > 0)
            $result .= strtolower($string[0]);
        if($length > 1)
            $result .= substr($string, 1);
        return $string;
    }

    public static function Init($dbConfig)
    {
        self::$DbConfig = $dbConfig;
        self::$DataHelper = self::getEasySQLObject(self::$DbConfig);
    }

    public static function Dispose()
    {
        if(self::$DataHelper && self::$Connection)
            self::$DataHelper->close();

        self::$DbConfig = null;
        self::$Connection = null;
        self::$DataHelper = null;
    }

    protected static $Connection = null;
    protected static $DataHelper = null;
    protected static $DbConfig = null;

    protected static function GetEasySQLObject($dbConfig)
    {
        if($dbConfig == null)
            $dbConfig = self::$DbConfig;
        $dataHelper = new EasySQL("MySQL", $dbConfig["server"],
            $dbConfig["user"], $dbConfig["password"], $dbConfig["name"]);
        if(self::$Connection)
        {
            $dataHelper->setConnection(self::$Connection);
        }
        else
        {
            $dataHelper->connect();
            self::$Connection = $dataHelper->getConnection();
        }

        return $dataHelper;
    }

    protected static function Convert($dbRecord)
    {
        $entity = array();
        foreach($dbRecord as $key => $value)
        {
            if(!is_int($key))
                $entity[$key] = $value;
        }

        return $entity;
    }

    protected $dataHelper = null;

    // record - Start

    protected $record = array("id" => 0, "created" => "", "modified" => "");

    private $table = array(
        "name" => "",
        "structure" => array(
            "id" => "integer",
            "created" => "string",
            "modified" => "string"
        ),
        "relationship" => array(
            "toOne" => array(),
            "toMany" => array()
        )
    );

    private $relations = array();
    private $cascade = false;
    private $keys = array("id");
    private $dataObjects = array();

    // record - End

    function __construct($table, $dbConfig = null, $record = null, $cascade = true)
    {
        $this->dataHelper = self::GetEasySQLObject($dbConfig);
        $this->initTable($table, $cascade);
        if($record)
            $this->set($record);
    }

    protected function initTable($table, $cascade)
    {
        $this->table = CommonTools::arrayMerge($this->table, $table);

        $this->dataHelper->useTable($this->table["name"]);

        if($cascade)
        {
            if(empty($this->table["relationship"]))
                $this->table["relationship"] = array("toOne" => array(), "toMany" => array());
            if(empty($this->table["relationship"]["toOne"]))
                $this->table["relationship"]["toOne"] = array();
            if(empty($this->table["relationship"]["toMany"]))
                $this->table["relationship"]["toMany"] = array();
            $this->relations = CommonTools::arrayMerge($this->table["relationship"]["toOne"], $this->table["relationship"]["toMany"]);
            $dirname = dirname(__FILE__);
            foreach($this->relations as $key => $value)
            {
                $class = $key . "DO";
                require_once $dirname . "/$class.php";
                if(!empty($value["dependent"]))
                {
                    $this->cascade = true;
                    $this->keys[] = $value["key"];
                    if(empty($this->dataObjects[$key]))
                        $this->dataObjects[$key] = new $class();
                }
                else
                    $this->relations[$key]["dependent"] = false;
                if(empty($value["plural"]))
                    $this->relations[$key]["plural"] = $key . "s";
                if(isset($this->table["relationship"]["toOne"][$key]))
                    $this->relations[$key]["type"] = "toOne";
                else
                    $this->relations[$key]["type"] = "toMany";
            }
            $this->keys = array_unique($this->keys);
        }
    }

    public function reset()
    {
        $this->record = array("id" => 0, "created" => "", "modified" => "");
    }

    function __destruct()
    {
        self::Dispose();
    }

    private function getFieldType($field)
    {
        return isset($this->table["structure"][$field]) ? $this->table["structure"][$field] : "string";
    }

    private function ensureFieldValue($field, $value)
    {
        $type = $this->getFieldType($field);
        switch($type)
        {
        case "object":
            $value = serialize($value);
            break;
        case "integer":
            $value = intval($value);
            break;
        case "boolean":
            $value = $value ? 1 : 0;
            break;
        default:
            $value = (string)$value;
            break;
        }
        return $value;
    }

    private function ensureData($data)
    {
        foreach($data as $key => $value)
        {
            $data[$key] = $this->ensureFieldValue($key, $value);
        }
        return $data;
    }

    private function castFieldValue($field, $value)
    {
        $type = $this->getFieldType($field);
        switch($type)
        {
        case "object":
            $value = unserialize($value);
            break;
        case "integer":
            $value = intval($value);
            break;
        case "boolean":
            $value = $value == 0 ? false : true;
            break;
        default:
            $value = (string)$value;
            break;
        }
        return $value;
    }

    private function castData($data)
    {
        foreach($data as $key => $value)
        {
            $data[$key] = $this->castFieldValue($key, $value);
        }
        return $data;
    }

    // Common Methods - Start

    public function hasRecord()
    {
        return $this->record["id"] != 0;
    }

    public function set($arg1, $arg2 = null)
    {
        $argsLength = func_num_args();
        if($argsLength == 2)
            $this->record[$arg1] = $this->ensureFieldValue($arg1, $arg2);
        else
        {
            $arg1 = $this->ensureData($arg1);
            $this->record = array_merge($this->record, $arg1);
        }
    }
    
    public function get($arg = null)
    {
        if($arg != null)
        {
            $result = null;
            if(isset($this->record[$arg]))
                $result = $this->castFieldValue($arg, $this->record[$arg]);
            return $result;
        }
        else
            return $this->castData($this->record);
    }

    public function remove($key)
    {
        unset($this->record[$key]);
    }

    public function load($id = null, $selection = null)
    {
        if(!$id)
            $id = $this->get("id");
        $this->reset();
        if(!$id)
            return;

        $this->loadById(array("value" => $id, "unique" => true), $selection);
    }

    public function save()
    {
        $id = $this->get("id");
        $created = $this->get("created");

        $this->remove("id");
        $this->remove("created");

        $now = date("Y-m-d H:i:s");
        $this->set("modified", $now);
        foreach($this->record as $key => $item)
        {
            if(in_array($key, array_keys($this->table["structure"])))
                $this->dataHelper->setField($key, $item);
        }
        if(!$id)
        {
            $this->dataHelper->setField("created", $now);
            $this->dataHelper->insert();
            $this->set("id", $this->dataHelper->insertId());
        }
        else
        {
            $this->dataHelper->setWhere(array("field" => "id", "value" => $id));
            $this->dataHelper->update();
            $this->set(compact("id", "created"));
        }
    }

    public function delete($where = array(), $joins = array())
    {
        if(!$where)
        {
            $where = array("field" => "id", "value" => $this->get("id"));
        }
        if($this->cascade)
        {
            $records = $this->loadAll(null, null, $this->keys, $where, array(), $joins);
            foreach($records as $item)
            {
                $this->set($item);
                $this->deleteRelatedObjects();
            }
        }
        $this->dataHelper->setWhere($where);
        $this->dataHelper->setJoins($joins);
        $this->dataHelper->delete();
        $this->reset();
    }

    protected function deleteRelatedObjects()
    {
        foreach($this->relations as $key => $value)
        {
            if($value["dependent"])
            {
                $this->dataObjects[$key]->delete(array("field" => $value["foreignKey"], "value" => $this->get($value["key"])));
            }
        }
    }

    public function loadAll($page = null, $pageSize = null, $selection = null, $where = null,
        $orderBy = null, $joins = null)
    {
        $this->reset();

        if($pageSize == null)
            $pageSize = 20;
        if($selection == null)
            $selection = "*";
        if($where == null)
            $where = array();
        if($orderBy == null)
            $orderBy = array("field" => "id", "order" => "desc");
        if($joins == null)
            $joins = array();
        $this->dataHelper->setSelection($selection);
        $this->dataHelper->setJoins($joins);
        $this->dataHelper->setWhere($where);
        $this->dataHelper->setOrderBy($orderBy);
        if($page === null)
        {
            $this->dataHelper->select();
        }
        else
        {
            $this->dataHelper->setLimit(($page - 1) * $pageSize, $pageSize);
            $this->dataHelper->select();
        }
        $records = array();
        while($record = $this->dataHelper->fetchArray())
        {
            $record = self::Convert($record);
            $records[] = $record;
        }

        if(isset($records[0]))
            $this->record = $records[0];

        foreach($records as $key => $item)
            $records[$key] = $this->castData($item);
        return $records;
    }

    public function count($where = null, $joins = null)
    {
        if($where == null)
            $where = array();
        if($joins == null)
            $joins = array();
        $result = 0;
        $this->dataHelper->setJoins($joins);
        $this->dataHelper->setWhere($where);
        $this->dataHelper->setSelection(array("field" => "count(*)", "group" => true, "alias" => "record_count"));
        $this->dataHelper->select();
        $record = $this->dataHelper->fetchArray();
        if($record)
            $result = $record["record_count"];
        return $result;
    }

    // Magic Methods
    function __call($name, $arguments)
    {
        if(strpos($name, "loadBy") === 0)
        {
            $fieldName = substr($name, 6);
            if($fieldName)
            {
                if(!isset($arguments[1]))
                    $arguments[1] = null;
                if(!isset($arguments[2]))
                    $arguments[2] = null;
                if(!isset($arguments[3]))
                    $arguments[3] = null;
                if(!isset($arguments[4]))
                    $arguments[4] = null;

                $fieldName = self::lcfirst($fieldName);
                return $this->loadBy($fieldName, $arguments[0], $arguments[1], $arguments[2], $arguments[3], $arguments[4]);
            }
        }
        if(strpos($name, "loadRelated") === 0)
        {
            $plural = substr($name, 11);
            $class = "";
            foreach($this->relations as $key => $value)
            {
                if($value["plural"] == $plural)
                {
                    $class = $key;
                    break;
                }
            }
            if($class)
            {
                if(!isset($arguments[0]))
                    $arguments[0] = null;
                if(!isset($arguments[1]))
                    $arguments[1] = null;
                if(!isset($arguments[2]))
                    $arguments[2] = null;
                if(!isset($arguments[3]))
                    $arguments[3] = null;

                return $this->loadRelated($class, $arguments[0], $arguments[1], $arguments[2], $arguments[3]);
            }
        }

        return null;
    }

    protected function loadBy($field, $value, $selection = null, $where = null, $orderBy = null, $joins = null)
    {
        $operator = "=";
        $unique = false;
        if(is_array($value))
        {
            $temp = $value;
            $value = $temp["value"];
            if(isset($temp["operator"]))
                $operator = $temp["operator"];
            if(isset($temp["unique"]))
                $unique = $temp["unique"];
        }
        if($where)
        {
            if(!is_array(current($where)))
                $where = array($where);
        }
        else
            $where = array();
        $where[] = array("field" => $field, "value" => $value, "operator" => $operator);
        if($orderBy == null)
        { 
            $orderBy = array(
                array("field" => $field, "order" => "asc"), 
                array("field" => "id", "order" => "desc")
            );
        }
        $result = $this->loadAll(null, null, $selection, $where, $orderBy, $joins);
        if($unique)
        {
            if($this->hasRecord())
                $result = $this->get();
            else
                $result = null;
        }

        return $result;
    }

    protected function loadRelated($class, $selection = null, $where = null,
        $orderBy = null, $joins = null 
    )
    {
        $relation = $this->relations[$class];
        if(empty($this->dataObjects[$class]))
        {
            $className = $class . "DO";
            $this->dataObjects[$class] = new $className(null, array(), false);
        }
        if($relation["dependent"])
        {
            $result = $this->dataObjects[$class]->loadBy($relation["foreignKey"],
                array("value" => $this->get($relation["key"]), "unique" => $relation["type"] == "toOne"),
                $selection, $where, $orderBy, $joins
            );
        }
        else
        {
            $result = $this->dataObjects[$class]->loadBy($relation["foreignKey"],
                array("value" => $this->get($relation["key"]), "unique" => $relation["type"] == "toOne"),
                $selection, $where, $orderBy, $joins
            );
        }
        return $result;
    }
}
