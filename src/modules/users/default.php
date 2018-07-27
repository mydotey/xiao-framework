<?php
$this->showSearch();
if(count($records) > 0)
{
    ?>
<table>
	<thead>
		<tr>
			<th>Number</th>
            <th><a href="<?php echo $this->generateOrderLink("login"); ?>">Login</a></th>
			<th><a href="<?php echo $this->generateOrderLink("email"); ?>">Email</a></th>
			<th><a href="<?php echo $this->generateOrderLink("created"); ?>">Created</a></th>
			<th>Operations</th>
		</tr>
	</thead>
	<tbody>
	<?php
	for($i = 0; $i < count($records); $i++)
	{
	    printf('<tr%s><td>%s</td><td>%s</td><td>%s</td><td>%s</td><td>
            <a href="%s">Edit</a><a href="%s" onclick="return confirm(\'Are you sure you want to delete it?\');">Delete</a>
            </td></tr>', ($i + 1) % 2 == 0 ? ' class="odd"' : '',
            ($page - 1) * $pageSize + $i + 1, $records[$i]["login"], $records[$i]["email"], $records[$i]["created"],
            $this->getPage(array("action" => "edit", "id" => $records[$i]["id"]), array("order", "search")),
            $this->getPage(array("id" => $records[$i]["id"]))
	    );
	}
	?>
	</tbody>
</table>
	<?php
    $this->showPagination();
}
else
{
?>
<fieldset class="empty-data">
    <p>There are no records currently.</p>
</fieldset>
<?php
}

?>
