<?php
if($this->get("id"))
{
    if($this->get("id") == 1)
    {
        $this->setMessage("You don't have permission to access it.");
        header("Location: " . $this->getPage(array(), array("action", "id")));
        exit;
    }
}

require_once $this->dataObjectsPath . "UserDO.php";
$dataObject = new UserDO();
$id = intval($this->get("id"));
$dataObject->load($id);
if(!$dataObject->hasRecord())
{
    $this->setMessage("Sorry. The record you are editting has been deleted sometime.");
    header("Location: " . $this->getPage(array(), array("action", "id")));
    exit;
}

$capabilities = $dataObject->get("capabilities");
$login = $dataObject->get("login");
$password = "";
$email = $dataObject->get("email");

if($this->isPostBack())
{
    $password = trim($this->post["password"]);
    $email = trim($this->post["email"]);
    $capabilities = $this->post("capabilities");

    $error = false;
    if($email)
    {
        $dataObject->loadByEmail(
            array("value" => $email, "unique" => true), array("id"),
            array("field" => "id", "value" => $id, "operator" => "!=")
        );
        if($dataObject->hasRecord())
        {
            $this->setMessage("Email has been used by other.");
            $error = true;
        }
    }
    if($capabilities == null)
    {
        $this->setMessage("Please designate the new user a capability.");
        $error = true;
    }
    if(!$error)
    {
        $dataObject->set(compact("id", "email", "capabilities"));
        if($password)
            $dataObject->set("password", md5($password));
        $dataObject->save();

        $this->setMessage("The record has been saved.");
        header("Location: " . $this->getPage(array(), array("action", "id")));
        exit;
    }
}
$this->setData(compact("capabilities", "login", "email", "password"));
$this->setData("completeCapabilities", $this->getCapabilities());
