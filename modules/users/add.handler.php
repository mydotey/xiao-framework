<?php
$completeCapabilities = $this->getCapabilities();
$capabilities = array("dashboard", "networks", "campaigns", "iframes");

if($this->isPostBack())
{
    require_once $this->dataObjectsPath . "UserDO.php";
    $dataObject = new UserDO();

    $login = $this->post("login");
    $password = $this->post("password");
    $email = $this->post("email");
    $capabilities = $this->post("capabilities");

    $dataObject->loadByLogin(array("value" => $login, "unique" => true), array("id"));
    if($dataObject->hasRecord())
    {
        $this->setMessage("User Login has been exsitent.");
        $this->setData(compact("login", "password", "email"));
    }
    else
    {
        $email = trim($email);
        $error = false;
        if($email)
        {
            $dataObject->loadByEmail(array("value" => $email, "unique" => true), array("id"));
            if($dataObject->hasRecord())
            {
                $this->setMessage("Email has been exsitent.");
                $this->setData(compact("login", "password", "email"));
                $error = true;
            }
        }
        if($capabilities == null)
        {
            $this->setMessage("Please designate the new user a capability.");
            $this->setData(compact("login", "password", "email"));
            $error = true;
        }
        if(!$error)
        {
            $password = md5($password);
            $record = compact("login", "password", "email", "capabilities");
            $dataObject->set($record);
            $dataObject->save();

            $this->setMessage("The record has been added.");
            header("Location: " . $this->getPage(array(), array("action")));
            exit;
        }
    }
}

$this->setData(compact("completeCapabilities", "capabilities"));
