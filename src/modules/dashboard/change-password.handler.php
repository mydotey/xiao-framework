<?php
if($this->isPostBack())
{
    $oldPassword = md5($_POST["oldPassword"]);
    if($oldPassword != $currentUser["password"])
    {
        $this->setMessage("Your old password is not correct. Please check.");
    }
    else
    {
        require_once $this->dataObjectsPath . "UserDO.php";
        $dataObject = new UserDO();
        $password = md5($_POST["newPassword"]);
        $id = $currentUser["id"];
        $dataObject->set(compact("id", "password"));
        $dataObject->save();
        $dataObject->load();
        self::setSession("currentUser", $dataObject->get());

        $this->setMessage("Your password has been updated.");
    }
}
