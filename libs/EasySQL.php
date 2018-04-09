<?php



/**
 *  EasySQL 1.0
 *
 *  @author: Carlos Reche
 *  @email:  carlosreche@yahoo.com
 *
 *  Jan 4, 2005
 *
 *
 *  Bugs fixed:
 *  - Jan 29, 2005: "LIMIT" clause didn't work properly when using EasySQL::select() method. Now fixed.
 *
 *  2010, Modified by Quang, http://qzhao.me
 */
class EasySQL
{

    /*@#+
     *  @acess private
     */
    private $SQLDatabase; // (object)    Mixed type. Will have a different instance depending on database software
    private $SQLQuery;    // (object)    Creates SQL queries. Type: SQLQuery

    private $connection;  // (resource)  Connection with database
    private $result;      // (resource)  Resource identifier returned by SELECT query, or boolean if query was INSERT, UPDATE or DELETE
    private $total_rows;  // (int)       Number of rows selected or affected (inserted, updated or deleted) by the last query executed
    //@#+
    
    function EasySQL($SQLDatabase = "", $host = "", $user = "", $password = "", $db_name = "", $port = "")
    {

        // Configuration for default values (used when these parameters were NOT passed by constructor)

        $_default['SQLDatabase'] = "";          //  Default SQL software (E.g. "MySQL", "PostGreSQL", "SQLite")
        $_default['host']        = "localhost"; //  Host server of database
        $_default['user']        = "root";      //  User
        $_default['password']    = "";          //  Password
        $_default['db_name']     = "";          //  Database that will be selected
        $_default['port']        = "";          //  Server port. Leave empty for default.
        //--


        $SQLDatabase = ($SQLDatabase != "")  ?  (string)$SQLDatabase  :  $_default['SQLDatabase'];
        $host        = ($host != "")         ?  (string)$host         :  $_default['host'];
        $user        = ($user != "")         ?  (string)$user         :  $_default['user'];
        $password    = ($password != "")     ?  (string)$password     :  $_default['password'];
        $db_name     = ($db_name != "")      ?  (string)$db_name      :  $_default['db_name'];
        $port        = ($port != "")         ?  (int)$port            :  $_default['port'];



        // Defines this class' objects: SQLDatabase and SQLQuery

        $this->SQLQuery = new SQLQuery();

        if (preg_match("/^(0|mysql)$/i", trim($SQLDatabase)))
        {
            // MySQL

            if (!function_exists("mysql_connect"))
            {
                $this->error("MySQL library not found.");
                return false;
            }
            else if (!class_exists("EasyMySQL"))
            {
                $this->error("Could NOT load <strong>EasyMySQL</strong> class. Aborting <strong>" . __CLASS__ . "</strong> class execution.");
                return false;
            }

            $this->SQLDatabase = new EasyMySQL($host, $user, $password, $db_name, $port);
        }


        else if (preg_match("/^(1|pg|postgre(s|sql)?)$/i", trim($SQLDatabase)))
        {
            // PostGreSQL

            if (!function_exists("pg_connect"))
            {
                $this->error("PostGreSQL library not found.");
                return false;
            }
            else if (!class_exists("EasyPostGreSQL"))
            {
                $this->error("Could NOT load <strong>EasyPostGreSQL</strong> class. Aborting <strong>" . __CLASS__ . "</strong> class execution.");
                return false;
            }

            $this->SQLDatabase = new EasyPostGreSQL($host, $user, $password, $db_name, $port);
        }


        else if (preg_match("/^(2|sqlite)$/i", trim($SQLDatabase)))
        {
            // SQLite

            if (!function_exists("sqlite_open"))
            {
                $this->error("SQLite library not found.");
                return false;
            }
            else if (!class_exists("EasySQLite"))
            {
                $this->error("Could NOT load <strong>EasySQLite</strong> class. Aborting <strong>" . __CLASS__ . "</strong> class execution.");
                return false;
            }

            $this->SQLDatabase = new EasySQLite($host, $user, $password, $db_name, $port);
        }

        else
        {
            $this->error("No SQL databases were defined! Aborting <strong>" . __CLASS__ . "</strong> class execution.");
            return false;
        }



        // Sets default values

        $this->connection = false;
        $this->result     = false;
        $this->total_rows = 0;
    }


    public function setConnection($connection)
    {
        $this->connection = $connection;
    }

    public function getConnection()
    {
        return $this->connection;
    }



    /*@#+
     *  @acess public
     */
    public function connect($db_name = "", $permanent = false)
    {
        if($this->connection)
            $this->close();

        $db_name = ($db_name != "")  ?  $db_name  :  $this->SQLDatabase->db_name;

        $this->SQLDatabase->connect($this->connection, $db_name, $permanent);

        return (bool)$this->connection;
    }




    public function close()
    {
        $result = true;
        if($this->connection)
        {
            $result = $this->SQLDatabase->close($this->connection);
            $this->connection = null;
        }
        return $result;
    }



    private function query($query)
    {
        if (!$this->connection)
        {
            $this->error("Please connect first.");
            return false;
        }


        $this->result     = false;
        $this->total_rows = 0;

        $this->result = $this->SQLDatabase->query($query, $this->connection);

        if (!$this->result)
        {
            $this->error();
            return false;
        }

        if ($this->result === true)
        {
            $this->total_rows = $this->SQLDatabase->affectedRows($this->result, $this->connection);
        }


        return $this->result;
    }



    public function affectedRows()
    {
        return $this->total_rows;
    }



    public function insertId()
    {
        return $this->SQLDatabase->insertId();
    }


    public function fetchArray($result = "")
    {
        if ($result == "")
        {
            $result =& $this->result;
        }

        return $this->SQLDatabase->fetchArray($result);
    }



    public function fetchRow($result = "")
    {
        if ($result == "")
        {
            $result =& $this->result;
        }

        return $this->SQLDatabase->fetchRow($result);
    }



    public function fetchAssoc($result = "")
    {
        if ($result == "")
        {
            $result =& $this->result;
        }

        return $this->SQLDatabase->fetchAssoc($result);
    }



    public function error($message = "")
    {
        if ($message == "")
        {
            $message = ($this->connection)  ?  $this->SQLDatabase->error($this->connection)  :  "Could not connect to database.";
        }

        echo '<br /><span style="padding: 1px 7px 1px 7px; background-color: #ffd7d7; font-family: verdana; color: #000000; font-size: 13px;"><span style="color: #ff0000; font-weight: bold;">Error!</span> ' . $message . '</span><br />';
    }





    public function useDatabase($db_name)
    {
        $this->SQLQuery->reset();
        $this->SQLDatabase->db_name = (string)$db_name;
    }



    public function useTable($table)
    {
        $this->SQLQuery->reset();
        $this->SQLQuery->table = (string)$table;
    }


    public function escape($value)
    {
        return $this->SQLDatabase->escape($value);
    }

    public function setField($field, $value, $is_sql_function = false)
    {
        $field = "`" . $field . "`";
        $value = $this->SQLDatabase->escape($value);

        $this->SQLQuery->values[$field]['value']           = $value;
        $this->SQLQuery->values[$field]['is_sql_function'] = (bool)$is_sql_function;
    }



    public function setFields($fields)
    {
        foreach($fields as $field)
        {
            if(!isset($field[2]))
                $field[2] = false;
            $this->setField($field[0], $field[1], $field[2]);
        }
    }


    public function setWhere($where, $operator = "and")
    {
        $this->SQLQuery->where = $this->generateWhere($where, $operator);
    }

    private function generateWhere($where, $operator = "and", $table = "")
    {
        $whereClause = "";
        if(is_string($where))
            $whereClause = $where;
        else if(is_array($where))
        {
            $item = current($where);
            if($item && !is_array($item))
                $where = array($where);
            $whereClause = $this->generateWhereClause($where, $operator, $table);
        }
        return $whereClause;
    }

    private function generateWhereClause($where, $operator = "", $table = "")
    {
        $result = "";
        $operators = array("and", "or");
        if(in_array($operator, $operators))
        {
            foreach($where as $key => $value)
            {
                if($result != "")
                    $result .= " $operator ";
                $key = strtolower($key);
                $result .= "(" . $this->generateWhereClause($value, in_array($key, $operators) ? $key : "", $table) . ")";
            }
        }
        else
        {
            if(!isset($where["operator"]))
                $where["operator"] = "=";
            if(empty($where["isJoin"]))
            {
                if(in_array(strtolower($where["operator"]), array("in", "not in")))
                {
                    $value = "(";
                    foreach($where["value"] as $item)
                    {
                        if($value != "(")
                            $value .= ",";
                        $value .= $this->escape($item);
                    }
                    $value .= ")";
                }
                else
                    $value = "'" . $this->escape($where["value"]) . "'";
                if($value == "()")
                    $result .= "1 = 1";
                else
                    $result .= sprintf("`%s`.`%s` %s %s", $table ? $table : $this->SQLQuery->table, $where["field"],
                        $where["operator"], $value);
            }
            else
                $result .= sprintf("`%s`.`%s` %s `%s`.`%s`", $table ? $table : $this->SQLQuery->table, $where["field"], $where["operator"],
                    isset($where["join-table-alias"]) ? $where["join-table-alias"] : $this->SQLQuery->table, $where["value"]);
        }

        return $result;
    }

    public function setOrderBy($orderBy)
    {
        $result = "";
        if(is_string($orderBy))
            $result = $orderBy;
        else if(is_array($orderBy))
        {
            $item = current($orderBy);
            if($item && !is_array($item))
                $orderBy = array($orderBy);
            foreach($orderBy as $item)
            {
                if($result != "")
                    $result .= ", ";
                $result .= sprintf("`%s`.`%s` %s", empty($item["table-alias"]) ? $this->SQLQuery->table : $item["table-alias"],
                    $item["field"], $item["order"]);
            }
        }

        $this->SQLQuery->orderBy = $result;
    }

    public function setLimit($start, $amount = "")
    {
        $limit = (string)$start;
        if($amount)
            $limit .= ", " . $amount;
        $this->SQLQuery->limit = $limit;
    }

    public function setSelection($selection)
    {
        $result = "";
        if($selection == null)
            $selection = "*";
        if($selection == "*")
            $selection = "`" . $this->SQLQuery->table . "`.*";
        if(is_string($selection))
            $result = $selection;
        else if(is_array($selection))
        {
            $item = each($selection);
            reset($selection);
            if($item["value"] && !is_array($item["value"]))
            {
                if(is_string($item["key"]))
                    $selection = array($selection);
                else
                {
                    $temp = array();
                    foreach($selection as $item2)
                    {
                        $temp[] = array("field" => $item2);
                    }
                    $selection = $temp;
                }
            }
            foreach($selection as $item)
            {
                if($result != "")
                    $result .= ", ";
                if(empty($item["group"]))
                {
                    $result .= "`";
                    if(isset($item["table-alias"]))
                        $result .= $item["table-alias"];
                    else
                        $result .= $this->SQLQuery->table;
                    $result .= "`.`" . $item["field"] . "`";
                }
                else
                    $result .= $item["field"];
                if(isset($item["alias"]))
                    $result .= " as `" . $item["alias"] . "`";
            }
        }
        $this->SQLQuery->selection = $result;
    }

    public function setJoins($joins = array())
    {
        $result = "";
        if($joins)
        {
            $joinType = array("inner", "left", "right");
            foreach($joinType as $item)
            {
                if(isset($joins[$item]))
                {
                    foreach($joins[$item] as $item2)
                    {
                        $result .= sprintf(" %s join `%s` as `%s` on (%s) ", $item,
                            $item2["table"], $item2["alias"], $this->generateWhere($item2["where"], "and", $item2["alias"]));
                    }
                }
            }
        }
        $this->SQLQuery->joins = $result;
    }





    public function select()
    {
        $query = $this->SQLQuery->createSelect();
        $this->SQLQuery->resetQuery();
        return $this->query($query);
    }



    public function insert()
    {
        $query = $this->SQLQuery->createInsert();
        $this->SQLQuery->resetQuery();
        return $this->query($query);
    }



    public function update()
    {
        $query = $this->SQLQuery->createUpdate();
        $this->SQLQuery->resetQuery();
        return $this->query($query);
    }


    public function delete()
    {
        $query = $this->SQLQuery->createDelete();
        $this->SQLQuery->resetQuery();
        return $this->query($query);
    }



    public function truncate()
    {
        $query = $this->SQLQuery->createTruncate();
        $this->SQLQuery->resetQuery();
        return $this->query($query);
    }
    //@#+

}







class SQLQuery
{

    var $query;     // (string)  Last query created

    var $table;     // (string)  Table used
    var $joins;     // (string)  Table Joins
    var $values;    // (array)   Each element is a new array wich "keys" are the field names. Each new array has to elements: (string) "value" and (bool) "is_sql_function"
    var $where;     // (string)  "WHERE" clause
    var $orderBy;
    var $limit;     // (string)  "LIMIT" clause
    var $selection; // (string)  Selection of select clause: "SELECT (selection) FROM table;"


    function SQLQuery()
    {
        $this->reset();
    }

    function reset()
    {
        $this->table        = "";
        $this->resetQuery();
    }



    public function resetQuery()
    {
        $this->query        = "";
        $this->values       = array();
        $this->where        = "";
        $this->orderBy      = "";
        $this->limit        = "";
        $this->selection    = "*";
    }



    function createSelect()
    {
        if ($this->selection == ""  ||  $this->table == "")
        {
            return false;
        }

        $this->query  = "SELECT " . $this->selection . " FROM " . $this->table
            . $this->joins
            . $this->returnWhere()
            . $this->returnOrderBy()
            . $this->returnLimit();

        return $this->query;
    }



    function createInsert()
    {
        if ($this->table == ""  ||  count($this->values) == 0)
        {
            if ($this->table == "") {
                $this->error('Could NOT create "INSERT" query. Parameter <strong>"table"</strong> was empty.');
            }
            if (count($this->values) == 0) {
                $this->error('Could NOT create "INSERT" query. Parameter <strong>"values"</strong> was empty.');
            }

            return false;
        }


        $values = $fields = array();

        foreach ($this->values as $fieldName => $fieldSettings)
        {
            $fields[] = $fieldName;

            if ($fieldSettings['value'] === NULL) {
                $values[] = "NULL";
            } else if ($fieldSettings['is_sql_function']) {
                $values[] = $fieldSettings['value'];
            } else {
                $values[] = "'" . $fieldSettings['value'] . "'";
            }
        }

        $values = " (" .   implode(', ', $fields)   . ") VALUES (" .   implode(', ', $values)   . ")";

        $this->query = "INSERT INTO " . $this->table . $values;



        return $this->query;
    }



    function createUpdate()
    {
        if ($this->table == ""  ||  count($this->values) == 0  ||  $this->where == "")
        {
            if ($this->table == "") {
                $this->error('Could NOT create "UPDATE" query. Parameter <strong>"table"</strong> was empty.');
            }
            if (count($this->values) == 0) {
                $this->error('Could NOT create "UPDATE" query. Parameter <strong>"values"</strong> was empty.');
            }
            if ($this->where == "") {
                $this->error('Safety procedure: "UPDATE" query was not created because <strong>"where"</strong> clause was empty.');
            }

            return false;
        }


        $values = $fields = array();

        foreach ($this->values as $fieldName => $fieldSettings)
        {
            if ($fieldSettings['value'] === NULL) {
                $values[] = $fieldName . " = NULL";
            } else if ($fieldSettings['is_sql_function']) {
                $values[] = $fieldName . " = " . $fieldSettings['value'];
            } else {
                $values[] = $fieldName . " = '" . $fieldSettings['value'] . "'";
            }
        }

        $values = " SET " .   implode(', ', $values);

        $this->query = "UPDATE " . $this->table . $values . $this->returnWhere() . $this->returnLimit();

        return $this->query;
    }



    function createDelete()
    {
        if ($this->table == ""  ||  $this->where == "")
        {
            if ($this->table == "") {
                $this->error('Could NOT create "DELETE" query. Parameter <strong>"table"</strong> was empty.');
            }
            if ($this->where == "") {
                $this->error('Safety procedure: "DELETE" query was not created because <strong>"where"</strong> clause was empty.');
            }

            return false;
        }

        $this->query = "DELETE FROM " . $this->table . $this->returnWhere() . $this->returnLimit();

        return $this->query;
    }




    function createTruncate()
    {
        if ($this->table == "")
        {
            $this->error('Could NOT create "TRUNCATE" query. Parameter <strong>"table"</strong> was empty.');
            return false;
        }

        $this->query = "TRUNCATE TABLE " . $this->table;
        return $this->query;
    }



    function returnWhere()
    {
        $where = ($this->where != "")  ?  (" WHERE (" . $this->where . ")")  :  "";
        return $where;
    }

    function returnOrderBy()
    {
        $orderBy = $this->orderBy  ?  " ORDER BY " . $this->orderBy  :  "";
        return $orderBy;
    }

    function returnLimit()
    {
        $limit = ($this->limit != "")  ?  (" LIMIT " . $this->limit)  :  "";
        return $limit;
    }

    function error($message = "")
    {
        echo '<br /><span style="padding: 1px 7px 1px 7px; background-color: #ffd7d7; font-family: verdana; color: #000000; font-size: 13px;"><span style="color: #ff0000; font-weight: bold;">Error!</span> ' . $message . '</span><br />';
    }

}







class EasyMySQL
{

    var $host;     // (string)  Host server of database
    var $user;     // (string)  User
    var $password; // (string)  Password
    var $db_name;  // (string)  Database that will be selected
    var $port;     // (int)     Server port



    function EasyMySQL($host = "", $user = "", $password = "", $db_name = "", $port = "")
    {
        $this->host     = ($host != "")      ?  (string)$host      :  "localhost";
        $this->user     = ($user != "")      ?  (string)$user      :  "root";
        $this->password = ($password != "")  ?  (string)$password  :  "";
        $this->db_name  = ($db_name != "")   ?  (string)$db_name   :  "";
        $this->port     = ($port != "")      ?  (int)$port         :  3306;
    }



    function connect(&$connection, $db_name, $is_persistent = false)
    {
        $db_name = ($db_name != "")  ?  $db_name  :  $this->db_name;

        if (!$is_persistent) {
            $connection = @mysql_connect($this->host.':'.$this->port, $this->user, $this->password);
        } else {
            $connection = @mysql_pconnect($this->host.':'.$this->port, $this->user, $this->password);
        }

        if (!$connection  ||  !@mysql_select_db($db_name, $connection))
        {
            return false;
        }

        return $connection;
    }



    function close(&$connection)
    {
        if ($connection)
        {
            return @mysql_close($connection);
        }

        return true;
    }



    function query($query, $connection)
    {
        return @mysql_query($query, $connection);
    }



    function numRows($result)
    {
        return @mysql_num_rows($result);
    }



    function affectedRows($result, $connection)
    {
        return @mysql_affected_rows($connection);
    }



    function fetchArray($result)
    {
        return @mysql_fetch_array($result);
    }



    function fetchRow($result)
    {
        return @mysql_fetch_row($result);
    }



    function fetchAssoc($result)
    {
        return @mysql_fetch_assoc($result);
    }



    function escape($string)
    {
        return mysql_real_escape_string($string);
    }



    function error($connection)
    {
        return @mysql_error($connection);
    }

    function insertId()
    {
        return mysql_insert_id();
    }

}





class EasyPostGreSQL
{

    var $host;     // (string)  Host server of database
    var $user;     // (string)  User
    var $password; // (string)  Password
    var $db_name;  // (string)  Database that will be selected
    var $port;     // (int)     Server port



    function EasyPostGreSQL($host = "", $user = "", $password = "", $db_name = "", $port = "")
    {
        $this->host     = ($host != "")      ?  (string)$host      :  "localhost";
        $this->user     = ($user != "")      ?  (string)$user      :  "root";
        $this->password = ($password != "")  ?  (string)$password  :  "";
        $this->db_name  = ($db_name != "")   ?  (string)$db_name   :  "";
        $this->port     = ($port != "")      ?  (int)$port         :  5432;
    }



    function connect(&$connection, $db_name, $is_persistent = false)
    {
        $db_name = ($db_name != "")  ?  $db_name  :  $this->db_name;

        $connection_string = 'host=' . $this->host . ' port=' . $this->port . ' user=' . $this->user . ' password=' . $this->password . ' dbname=' . $db_name;


        if (!$is_persistent) {
            $connection = @pg_connect($connection_string, PGSQL_CONNECT_FORCE_NEW);
        } else {
            $connection = @pg_pconnect($connection_string, PGSQL_CONNECT_FORCE_NEW);
        }

        if (!$connection)
        {
            return false;
        }

        return $connection;
    }



    function close(&$connection)
    {
        if ($connection)
        {
            return @pg_close($connection);
        }

        return true;
    }



    function query($query, $connection)
    {
        return @pg_query($connection, $query);
    }



    function affectedRows($result, $connection)
    {
        return @pg_affected_rows($result);
    }



    function numRows($result)
    {
        return @pg_num_rows($result);
    }



    function fetchArray($result)
    {
        return @pg_fetch_array($result);
    }



    function fetchRow($result)
    {
        return @pg_fetch_row($result);
    }



    function fetchAssoc($result)
    {
        return @pg_fetch_assoc($result);
    }



    function escape($string)
    {
        return @pg_escape_string($string);
    }



    function error($connection)
    {
        return @pg_last_error($connection);
    }

    function insertId()
    {
        return @pg_last_oid();
    }

}







class EasySQLite
{

    var $host;     // (string)  This parameter is not used on SQLite
    var $user;     // (string)  This parameter is not used on SQLite
    var $password; // (string)  This parameter is not used on SQLite
    var $db_name;  // (string)  Database that will be selected
    var $port;     // (int)     This parameter is not used on SQLite



    function EasySQLite($host = "", $user = "", $password = "", $db_name = "", $port = "")
    {
        $this->host     = NULL;
        $this->user     = NULL;
        $this->password = NULL;
        $this->port     = NULL;

        if ($db_name != "") {
            $this->db_name = (string)$db_name;
        } else if ($host != "") {
            $this->db_name = (string)$host;
        } else {
            $this->db_name = "";
        }
    }



    function connect(&$connection, $db_name, $is_persistent = false)
    {
        $db_name = ($db_name != "")  ?  $db_name  :  $this->db_name;

        if (!$is_persistent) {
            $connection = @sqlite_open($db_name, 0666, $error_message);
        } else {
            $connection = @sqlite_popen($db_name, 0666, $error_message);
        }

        if (!$connection)
        {
            echo $error_message;
            return false;
        }

        return $connection;
    }



    function close(&$connection)
    {
        if ($connection)
        {
            return @sqlite_close($connection);
        }

        return true;
    }



    function query($query, $connection)
    {
        return @sqlite_query($connection, $query);
    }



    function affectedRows($result, $connection)
    {
        return @sqlite_changes($connection);
    }



    function numRows($result)
    {
        return @sqlite_num_rows($result);
    }



    function fetchArray($result)
    {
        return @sqlite_fetch_array($result, SQLITE_BOTH);
    }



    function fetchRow($result)
    {
        return @sqlite_fetch_array($result, SQLITE_NUM);
    }



    function fetchAssoc($result)
    {
        return @sqlite_fetch_array($result, SQLITE_ASSOC);
    }



    function escape($string)
    {
        return @sqlite_escape_string($string);
    }



    function error($connection)
    {
        return @sqlite_error_string(@sqlite_last_error($connection));
    }

    function insertId()
    {
        return @sqlite_last_insert_rowid();
    }
}



?>
