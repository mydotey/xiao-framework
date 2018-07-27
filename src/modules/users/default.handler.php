<?php
require_once $this->dataObjectsPath . "UserDO.php";
$dataObject = new UserDO();

if($this->get("id"))
{
    if($this->get("id") == 1)
    {
        $this->setMessage("You don't have permission to access it.");
    }
    else
    {
        $dataObject->load($this->get("id"));
        $dataObject->delete();
        $this->setMessage("The record has been deleted.");
    }
    header("Location: " . $this->getPage(array(), array("id")));
    exit;
}

$where = array(
    array("field" => "id", "value" => 1, "operator" => "!=")
);
$search = trim($this->get("search"));
if($search)
{
    $where[] = array("field" => "login", "value" => "%$search%", "operator" => "like");
    $this->setData("search", $search);
}
$recordCount = $dataObject->count($where);
$paginationInfo = $this->preparePagination($recordCount);
$orderBy =  $this->prepareOrders();
$records = $dataObject->loadAll($paginationInfo["page"],
    $paginationInfo["pageSize"], null, $where, $orderBy
);
$this->setData("records", $records);
