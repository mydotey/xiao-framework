<?php
require_once $this->dataObjectsPath . "UserDO.php";
$dataObject = new UserDO();
if($this->isPostBack())
{
    $email = trim($this->post["email"]);
    $dataObject->loadByEmail(
        array("value" => $email, "unique" => true), null,
        array("field" => "id", "value" => $currentUser["id"], "operator" => "!=")
    );
    $error = false;
    if($dataObject->hasRecord())
    {
        $this->setMessage("The email has been used by other.");
        $error = true;
    }
    if(!$error)
    {
        $dataObject->set(array(
            "id" => $currentUser["id"],
            "email" => $email
        ));
        $dataObject->save();
        $this->setMessage("Your account info has been saved.");
        $saved = true;
    }
}

$dataObject->load($currentUser["id"]);
if(!empty($saved))
    self::setSession("currentUser", $dataObject->get());
$this->setData("me", $dataObject->get());
