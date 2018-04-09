<?php
/* PHP Xiao N-Tier Framework
 * Author: Qiang Zhao
 * Version: 2.2
 * Contact: qzhao4@gmail.com
 * Site: http://qzhao.me
 */

class Page
{
    // Static Members - Start

    private static function stripSlashes($value)
    {
        $result = null;
        if(is_string($value))
            $result = stripSlashes($value);
        else if(is_array($value))
        {
            $result = array();
            foreach($value as $key2 => $value2)
            {
                $result[$key2] = self::stripSlashes($value2);
            }
        }

        return $result;
    }

    private static function encodeSpecialChars($param)
    {
        if(is_array($param))
        {
            $result = array();
            foreach($param as $key => $value)
            {
                $result[$key] = self::encodeSpecialChars($value);
            }
        }
        else if(is_string($param))
        {
            $result = htmlspecialchars($param);
        }
        else
            $result = $param;

        return $result;
    }

    private static function decodeSpecialChars($param)
    {
        if(is_array($param))
        {
            $result = array();
            foreach($param as $key => $value)
            {
                $result[$key] = self::decodeSpecialChars($value);
            }
        }
        else if(is_string($param))
        {
            $result = htmlspecialchars_decode($param);
        }
        else
            $result = $param;

        return $result;
    }
    
    private static function changeUrl($url, $addedParameters = array(), $deletedParameters = array())
    {
        $urlParts = explode("?", $url);
        if(!isset($urlParts[1]))
            $urlParts[1] = "";
        $parameterParts = explode("&", $urlParts[1]);
        $parameters = array();
        foreach($parameterParts as $item)
        {
            $parameter = explode("=", $item);
            if($parameter[0])
                $parameters[$parameter[0]] = isset($parameter[1]) ? $parameter[1] : "";
        }
        foreach($addedParameters as $key => $value)
        {
            $addedParameters[$key] = urlencode($value);
        }
        $parameters = array_merge($parameters, $addedParameters);
        foreach($deletedParameters as $item)
        {
            unset($parameters[$item]);
        }
        $queryString = "";
        foreach($parameters as $key => $value)
        {
            if($queryString != "")
                $queryString .= "&";
            $queryString .= $key . "=" . $value;
        }
        $newUrl = $urlParts[0];
        if($queryString != "")
            $newUrl .= "?" . $queryString;

        return $newUrl;
    }
    
    private static function sessionStart()
    {
        if(session_id())
        {
            self::sessionEnd();
        }

        session_start();
    }

    private static function setSession($param1, $param2 = "")
    {
        if(is_array($param1))
        {
            foreach($param1 as $key => $value)
            {
                self::setSession($key, $value);
            }
        }
        else
        {
            $key = $param1;
            $value = $param2;
            $_SESSION[$key] = $value;
        }
    }

    private static function getSession($key)
    {
        $value = null;
        if(isset($_SESSION[$key]))
            $value = $_SESSION[$key];
        return $value;
    }

    private static function deleteSession($param)
    {
        if(is_array($param))
        {
            foreach($param as $item)
            {
                self::deleteSession($item);
            }
        }
        else
        {
            unset($_SESSION[$param]);
        }
    }

    private static function sessionEnd()
    {
        session_unset();
        session_destroy();
    }

    // Static Member - End

    private $appRoot = "";

    private $config = array();

    private $request = array();
    private $post = array();
    private $get = array();

    private $module = ".";
    private $action = "default";

    private $title = "";
    private $capability = "anonymous";

    private $styles = array();
    private $scripts = array();

    private $webRoot = "";
    private $imagesUrl = "";
    private $scriptsUrl = "";
    private $stylesUrl = "";
    private $utilitiesUrl = "";

    private $stylesPath = "";
    private $scriptsPath = "";
    private $libsPath = "";
    private $dataObjectsPath = "";
    private $businessObjectsPath = "";
    private $utilitiesPath = "";

    private $dataToRender = array();
    private $dataToDebug = array();

    public function __construct($siteRoot, $styles = array(), $scripts = array())
    {
        $this->appRoot = $siteRoot;
        require_once $this->appRoot . "libs/CommonTools.php";

        $this->styles = $styles;
        $this->styles["site"] = "site.css";

        $this->scripts["jQuery"] = "jquery.js";
        $this->scripts = CommonTools::arrayMerge($this->scripts, $scripts);
        $this->scripts["site"] = "site.js";
    }

    // Page Init - Start

    public function init()
    {
        $this->prepareRequest();
        $this->route();
        $this->loadConfig();
        $defaultPage = $this->getConfig("defaultPage");
        if(isset($defaultPage["anonymous"]))
        {
            if($this->module == ".")
            {
                $this->module = $defaultPage["anonymous"]["module"];
                if(isset($defaultPage["anonymous"]["action"]))
                    $this->action = $defaultPage["anonymous"]["action"];
            }
        }

        $moduleConfig = $this->getConfig("modules");
        // title
        if(!empty($moduleConfig[$this->module]["actions"][$this->action]["title"]))
            $this->title = $moduleConfig[$this->module]["actions"][$this->action]["title"];
        else if(!empty($moduleConfig[$this->module]["title"]))
            $this->title = $moduleConfig[$this->module]["title"];
        else
            $this->title = $this->getConfig("title");
        // capability
        if(isset($moduleConfig[$this->module]["actions"][$this->action]["capability"]))
            $this->capability = $moduleConfig[$this->module]["actions"][$this->action]["capability"];
        else if(isset($moduleConfig[$this->module]["capability"]))
            $this->capability = $moduleConfig[$this->module]["capability"];

        $this->stylesPath = $this->appRoot . "styles/";
        $this->scriptsPath = $this->appRoot . "scripts/";
        $this->libsPath = $this->appRoot . "libs/";
        $this->dataObjectsPath = $this->appRoot . "data-access/";
        $this->businessObjectsPath = $this->appRoot . "business/";
        $this->utilitiesPath = $this->appRoot . "utilities/";

        $this->webRoot = $this->getConfig("webRoot") . "/";
        $this->imagesUrl = $this->webRoot . "images/";
        $this->scriptsUrl = $this->webRoot . "scripts/";
        $this->stylesUrl = $this->webRoot . "styles/";
        $this->utilitiesUrl = $this->webRoot . "utilities/";
    }

    private function prepareRequest()
    {
        $this->get = $_GET;
        $this->post = $_POST;
        if(get_magic_quotes_gpc())
        {
            $this->get = self::stripSlashes($this->get);
            $this->post = self::stripSlashes($this->post);
        }

        $this->request = array_merge($this->get, $this->post);
    }

    private function route()
    {
        if(isset($this->get["module"]))
            $this->module = trim(strtolower($this->get["module"]));
        if(isset($this->get["action"]))
            $this->action = trim(strtolower($this->get["action"]));
    }

    private function loadConfig()
    {
        $configFiles = array($this->appRoot . "config.php");
        if($this->module != ".")
            $configFiles[] = $this->appRoot . "modules/" . $this->module . "/config.php";
        $configFiles[] = $this->appRoot . "modules/" . $this->module . "/" . $this->action . ".config.php";
        foreach($configFiles as $item)
        {
            if(file_exists($item))
            {
                require_once $item;
                $this->config = CommonTools::arrayMerge($this->config, $config);
            }
        }
    }

    private function getConfig($key, $type = "string")
    {
        $value = "";
        if(isset($this->config[$key]))
            $value = $this->config[$key];
        else if($type == "object")
            $value = null;
        else if($type == "number")
            $value = 0;
        else if($type == "boolean")
            $value = false;

        return $value;
    }

    private function getCapabilities()
    {
        $moduleConfig = $this->getConfig("modules");
        $capabilities = array();
        foreach($moduleConfig as $key => $value)
        {
            if(!empty($value["capability"]) && !in_array($value["capability"], $capabilities)
                && $value["capability"] != "anonymous")
                $capabilities[] = $value["capability"];
            if(!empty($value["actions"]))
            {
                foreach($value["actions"] as $key2 => $value2)
                {
                    if(!empty($value2["capability"]) && !in_array($value2["capability"], $capabilities)
                        && $value2["capability"] != "anonymous")
                        $capabilities[] = $value2["capability"];
                }
            }
        }

        return $capabilities;
    }

    // Page Init - End

    // Handle Business - Start
    
    private function get($key)
    {
        if(isset($this->get[$key]))
            return $this->get[$key];
        else
            return null;
    }

    private function post($key)
    {
        if(isset($this->post[$key]))
            return $this->post[$key];
        else
            return null;
    }

    private function request($key)
    {
        if(isset($this->request[$key]))
            return $this->request[$key];
        else
            return null;
    }

    private function getDefaultPage($userCapabilities = array())
    {
        $pageInfo = null;
        $userCapabilities = array_merge($userCapabilities, array("anonymous"));
        $defaultPage = $this->getConfig("defaultPage");
        foreach($defaultPage as $key => $value)
        {
            if(in_array($key, $userCapabilities))
            {
                $pageInfo = $value;
                break;
            }
        }

        $page = $this->webRoot . "index.php";
        if(isset($pageInfo["module"]))
        {
            $page .= "?module=" . urlencode($pageInfo["module"]);
            if(isset($pageInfo["action"]))
            {
                $page .= "&action=" . urlencode($pageInfo["action"]);
            }
        }

        return $page;
    }
    
    public function handleBusiness()
    {
        $this->initDebug();
        self::sessionStart();
        $this->setData("useTemplate", true);
        $this->setData("module", $this->module);
        $this->setData("action", $this->action);

        $currentUser = self::getSession("currentUser");
        $userCapabilities = array("anonymous");
        if($currentUser)
            $userCapabilities = $currentUser["capabilities"];
        if(!$this->hasAccessTo($userCapabilities))
        {
            if($currentUser)
            {
                $this->setMessage("Sorry. You have no permission to access it.");
                header("Location: " . $this->getDefaultPage($userCapabilities));
            }
            else
            {
                $this->setMessage("Sorry. Please login to use.");
                header("Location: " . $this->webRoot . "index.php?module=users&action=login");
            }
            exit;
        }
        $this->setData("isLoggedIn", $currentUser != null);

        if(file_exists($this->appRoot . "modules/" . $this->module . "/" . $this->action . ".handler.php"))
        {
            require_once $this->dataObjectsPath . "BaseDataObject.php";
            BaseDataObject::Init($this->getConfig("dbConfig"));

            require_once $this->appRoot . "modules/" . $this->module . "/" . $this->action . ".handler.php";

            BaseDataObject::Dispose();
        }

        $this->prepareMenuInfo($userCapabilities);

        $message = self::getSession("message");
        if($message)
        {
            $this->setData("message", $message);
            self::deleteSession("message");
        }

        if($this->isAjax())
            $this->notUseTemplate();
    }

    private function isPostBack()
    {
        return (bool)$this->post;
    }

    private function isAjax()
    {
        return isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest';
    }

    private function hasAccessTo($userCapabilities, $capability = "")
    {
        $hasAccess = false;
        if($capability == "")
            $capability = $this->capability;
        if($capability == "anonymous")
            $hasAccess = true;
        else if(in_array($capability, $userCapabilities))
            $hasAccess = true;
        return $hasAccess;
    }

    private function prepareMenuInfo($userCapabilities)
    {
        $moduleConfig = $this->getConfig("modules");
        $menu = array();
        foreach($moduleConfig as $key => $value)
        {
            $capability = "anonymous";
            if(isset($value["capability"]))
                $capability = $value["capability"];
            if(empty($value["actions"]["default"]["capability"]) && $this->hasAccessTo($userCapabilities, $capability)
                || !empty($value["actions"]["default"]["capability"]) && $this->hasAccessTo($userCapabilities, $value["actions"]["default"]["capability"])
            )
            {
                $menu[$value["menuOrder"]] = array("url" => $this->webRoot . "index.php?module=" . $key,
                    "title" => $value["title"], "subItems" => array());
                $actions = array();
                foreach($value["actions"] as $key2 => $value2)
                {
                    $capability2 = $capability;
                    if(isset($value2["capability"]))
                        $capability2 = $value2["capability"];
                    if($this->hasAccessTo($userCapabilities, $capability2))
                    {
                        if(empty($value2["asMainMenu"]))
                            $actions[] = $key2;
                        if(!empty($value2["asSubMenu"]))
                        {
                            $menu[$value["menuOrder"]]["subItems"][$value2["menuOrder"]] = array(
                                "url" => $this->webRoot . "index.php?module=" . $key . "&action=" . $key2,
                                "title" => $value2["title"], "isActive" => $this->module == $key && $this->action == $key2
                            );
                        }
                    }
                }
                $menu[$value["menuOrder"]]["isActive"] = $this->module == $key && in_array($this->action, $actions);
                ksort($menu[$value["menuOrder"]]["subItems"]);
            }
            foreach($value["actions"] as $key2 => $value2)
            {
                $capability2 = $capability;
                if(isset($value2["capability"]))
                    $capability2 = $value2["capability"];
                if(!empty($value2["asMainMenu"]) && $this->hasAccessTo($userCapabilities, $capability2))
                {
                    $menu[$value2["menuOrder"]] = array("url" => $this->webRoot . "index.php?module=" . $key . "&action=" . $key2, 
                        "title" => $value2["title"], "subItems" => array(), "isActive" => $this->module == $key && $this->action == $key2);
                }
            }
        }
        ksort($menu);

        $this->setData("menu", $menu);
        $currentModuleMenuOrder = isset($moduleConfig[$this->module]["menuOrder"]) ? $moduleConfig[$this->module]["menuOrder"] : "NaN";
        if(isset($menu[$currentModuleMenuOrder]))
            $this->setData("subMenu", $menu[$currentModuleMenuOrder]["subItems"]);
    }

    private function notUseTemplate()
    {
        $this->setData("useTemplate", false);
    }

    private function noSidebar()
    {
        $this->setData("noSidebar", true);
    }

    private function setTitle($title)
    {
        $this->title = $title;
    }

    private function setData($param1, $param2 = "")
    {
        if(is_array($param1))
        {
            foreach($param1 as $key => $value)
            {
                $this->setData($key, $value);
            }
        }
        else
        {
            $key = $param1;
            $value = $param2;
            $this->dataToRender[$key] = $value;
        }
    }

    private function setMessage($message)
    {
        self::setSession("message", $message);
    }

    private function prepareOrders()
    {
        if($this->get("order") != null)
        {
            $items = explode(";", $this->get("order"));
            $orders = array();
            foreach($items as $item)
            {
                if($item)
                {
                    $order = explode(",", $item);
                    $orders[$order[0]] = $order[1];
                }
            }
        }
        else
            $orders = array("id" => "desc");
        $this->setData(compact("orders"));

        $orderBy = array();
        foreach($orders as $key => $value)
        {
            $orderBy[] = array("field" => $key, "order" => $value);
        }
        return $orderBy;
    }

    private function preparePagination($recordCount)
    {
        $this->setData("recordCount", $recordCount);
        $pageCount = ceil($recordCount / (float)$this->getConfig("pageSize"));
        $page = 1;
        if(isset($this->get["page"]))
            $page = $this->get["page"];
        if($page > 1 && $page > $pageCount)
        {
            header("Location: " . $this->getPage(array("page" => $pageCount)));
            exit;
        }
        $pageSize = $this->getConfig("pageSize");
        $paginationInfo = compact("page", "pageCount", "pageSize");
        $this->setData($paginationInfo);
        return $paginationInfo;
    }

    private function initDebug()
    {
        if($this->getConfig("debug"))
        {
            ini_set("display_errors", true);
            error_reporting($this->getConfig("errorReporting"));
        }
        else
        {
            ini_set("display_errors", false);
        }
    }

    private function debug($value, $key = "")
    {
        if($key != "")
            $this->dataToDebug[$key] = $value;
        else
            $this->dataToDebug[] = $value;
    }

    // Handle Business - End

    // Page Render - Start

    private function prepareData()
    {
        $this->dataToRender = self::encodeSpecialChars($this->dataToRender);
        if(!$this->getData("noSidebar", "boolean"))
        {
            if($this->getData("subMenu", "array") == array())
                $this->setData("noSidebar", true);
        }
    }

    private function isLoggedIn()
    {
        return $this->getData("isLoggedIn", "boolean");
    }

    public function render()
    {
        $this->prepareData();
        extract($this->dataToRender);

        if($this->module != ".")
            $view = $this->appRoot . "modules/" . $this->module . "/" . $this->action . ".php";
        else
            $view = $this->appRoot . $this->action . ".php";

        if(!file_exists($view))
        {
            $view = $this->appRoot . "404.php";
        }

        if($this->getData("useTemplate"))
        {
            $header = $this->appRoot . "header.php";
            if(file_exists($header))
                require_once $header;

            $this->showMessage();
            require_once $view;
            $this->debugInfo();

            $footer = $this->appRoot . "footer.php";
            if(file_exists($footer))
                require_once $footer;
        }
        else
        {
            $this->showMessage();
            require_once $view;
            $this->debugInfo();
        }
    }

    private function showSearch()
    {
        $search = $this->getData("search");
        echo <<<html
        <fieldset class="search">
<form method="get" class="jNice">
    <input type="hidden" name="module" value="{$this->module}" />
    <input type="hidden" name="action" value="{$this->action}" />
    <input type="text" name="search" value="{$search}" class="text-long" />
    <button type="submit"><span><span>Search</span></span></button>
</form>
</fieldset>
html;
    }

    private function generateOrderLink($field)
    {
        $orders = $this->getData("orders");
        if(empty($orders[$field]))
            $orders[$field] = "asc";
        else if($orders[$field] == "asc")
            $orders[$field] = "desc";
        else
            $orders[$field] = "asc";
        $order = $field . "," . $orders[$field];
        unset($orders[$field]);
        foreach($orders as $key => $value)
        {
            $order .= ";" . $key . "," . $value;
        }
        return $this->getPage(array("order" => $order));
    }

    private function showPagination()
    {
        $html = "";
        $pageCount = $this->getData("pageCount");
        $page = $this->getData("page");
        if($pageCount > 1)
        {
            $html .= '<p class="paging">';
            $prevPage = $page - 1;
            if($prevPage > 0)
            {
                $html .= sprintf('<a href="%s">Previous</a>', $this->getPage(array("page" => $prevPage)));
            }
            $nextPage = $page + 1;
            if($nextPage <= $pageCount)
            {
                $html .= sprintf('<a href="%s">Next</a>', $this->getPage(array("page" => $nextPage)));
            }
            $html .= '</p>';
        }
        echo $html;
    }

    private function showMessage()
    {
        $message = $this->getData("message");
        if($message != "")
        {
            echo <<<html
<p id="message">$message</p>
<script type="text/javascript">
    jQuery(document).ready(function(){
        window.setTimeout(function(){
            jQuery("#message").hide(400);
        }, 3000);
    });
</script>
html;
        }
    }

    private function getTitle()
    {
        return $this->title;
    }

    private function getData($key, $type = "string")
    {
        if(isset($this->dataToRender[$key]))
            return $this->dataToRender[$key];
        else if($type == "array")
            return array();
        else if($type == "object")
            return null;
        else if($type == "number")
            return 0;
        else if($type == "boolean")
            return false;
        else
            return "";
    }

    private function applyStyles()
    {
        foreach($this->styles as $item)
        {
            if(stripos($item, "http") === 0)
                printf('<link rel="stylesheet" type="text/css" href="%s" />', $item);
            else if(file_exists($this->stylesPath . $item))
                printf('<link rel="stylesheet" type="text/css" href="%s" />', $this->stylesUrl . $item);
        }

        $this->styles = array();
    }

    private function applyScripts()
    {
        foreach($this->scripts as $item)
        {
            if(stripos($item, "http") === 0)
                printf('<script type="text/javascript" src="%s"></script>', $item);
            else if(file_exists($this->scriptsPath . $item))
                printf('<script type="text/javascript" src="%s"></script>', $this->scriptsUrl . $item);
        }

        $this->scripts = array();
    }

    private function debugInfo()
    {
        if($this->getConfig("debug") && $this->dataToDebug)
        {
            echo '<div id="debug">';
            echo '<pre>';
            print_r($this->dataToDebug);
            echo '</pre>';
            echo '</div>';
        }
    }

    private function getPage($addedParameters = array(), $deletedParameters = array())
    {
        if(!isset($_SERVER["HTTP_HOST"]) || !isset($_SERVER["REQUEST_URI"]))
            return "";

        $prefix = "http://";
        if(stripos($this->getConfig("webRoot"), "https://") === 0)
            $prefix = "https://";
        $referrer = $prefix . $_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"];
        $referrer = self::changeUrl($referrer, $addedParameters, $deletedParameters);

        return $referrer;
    }

    // Page Render - End

}
