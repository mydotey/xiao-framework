<?php
if($this->isLoggedIn())
{
    header("Location: " . $this->getDefaultPage($currentUser["capabilities"]));
    exit;
}
if($this->isPostBack())
{
    $login = $this->post["login"];
    $password = md5($this->post["password"]);

    require_once $this->dataObjectsPath . "UserDO.php";
    $dataObject = new UserDO();
    $dataObject->loadByLogin(
        array("value" => $login, "unique" => true), null,
        array("field" => "password", "value" => $password)
    );
    if($dataObject->hasRecord())
    {
        $currentUser = $dataObject->get();
        $dataObject->loadMetadata();
        $currentUser["metaData"] = $dataObject->getMetadata();
        self::setSession("currentUser", $currentUser);
        $this->setMessage("You have logged in.");
        header("Location: " . $this->getDefaultPage($currentUser["capabilities"]));
        exit;
    }

    $this->setMessage("Incorrect user login Or password. Please try again.");
    $this->setData("login", $login);
}
