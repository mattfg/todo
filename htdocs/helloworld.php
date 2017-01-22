<style>
.button {
  color:blue;
  border-style: solid;
  border-width: 1px;
  border-radius: 5px;
  padding: 4px;
}
</style>
<?php
require 'query.php';

if(array_key_exists("name", $_GET)) {
  $ownerName = $_GET["name"];
} else {
  printf("<h1>Please login at <a href='login.php'>the login page</a>.</h1>");
  die();
}

$userExists = false;
query("SELECT `id` FROM `users` WHERE `name`='".sqlEscape($ownerName)."'",
  #need a better way to choose this if there's more than one user with the same name; as is it chooses the most recent one it's given (last in the db)
  function($row) {
    global $ownerId, $userExists;
    $userExists = true;
    $ownerId = $row[0];
  }
);

while(!$userExists) {
  query("INSERT INTO `users` (`id`, `name`) VALUES (NULL, '".sqlEscape($ownerName)."')");
  query("SELECT `id` FROM `users` WHERE `name`='".sqlEscape($ownerName)."'",
    #need a better way to choose this if there's more than one user with the same name; as is it chooses the most recent one it's given (last in the db)
    function($row) {
      global $ownerId, $userExists;
      $userExists = True;
      $ownerId = $row[0];
    }
  );
}

$selectAll = "SELECT * FROM `tasks` WHERE `owner`=".sqlEscape($ownerId);

if(array_key_exists("command", $_GET)) {
  $command = $_GET["command"];
  if($command == 'add') {
    $title=sqlEscape($_GET['title']);
    $status=sqlEscape($_GET['status']);
    $duedate=sqlEscape($_GET['duedate']);
    $descript=sqlEscape($_GET['details']);
    query('INSERT INTO `tasks` (`id`, `title`, `status`, `duedate`, `owner`, `descript`) VALUES (NULL, "'
      .sqlEscape($title).'", "'
      .sqlEscape($status).'", "'
      .sqlEscape($duedate).'", "'
      .$ownerId.'", "'
      .sqlEscape($descript).'")'
    );
  } elseif ($command == 'edit') {
    $row = $_GET['rowNum'];
    $colName = $_GET['colName'];
    $value = $_GET['value'];
    query("UPDATE `tasks`"
      ."SET `".sqlEscape($colName)
      ."`='".sqlEscape($value)
      ."' WHERE `id`=".sqlEscape($row).';'
    );
  } elseif ($command == 'delete') {
    $row = $_GET['rownumber'];
    query("DELETE FROM `tasks` WHERE `id`='".sqlEscape($row)."'");
  } else {
    echo 'Unrecognized command.';
    die();
  }
}
#Display the results table.
$rownumber=0;
$tableModel=[];
query($selectAll,
  function($row) {
    global $rownumber, $tableModel;
    $tableModel[$rownumber++] = $row;
  }
);
?>
<table style='
  text-align: left;
  padding: 4px;
  border-collapse: collapse;
  width: 100%;
'>
  <tr>
  <th></th>
  <th>title</th>
  <th>status</th>
  <th>due date</th>
  <th>details</th>
  </tr>
<?php
foreach ($tableModel as $i => $row) {
  $id = $row[0];
  $title = $row[1];
  $status = $row[2];
  $duedate = $row[3];
  $descript = $row[5];
  printf('<tr style="border: 1px solid black;">');
  printf('<td>'.htmlEscape($id).'</td>');
  printf(
    '<td style="padding: 6px;">'
    .htmlEscape($title).
    '</td>');
  printf(
    '<td style="padding: 6px;">'
    .htmlEscape($status).
    '</td>');
  printf(
    '<td style="padding: 6px;">'
    .htmlEscape($duedate).
    '</td>');
  printf(
    '<td style="padding: 6px;">'
    .htmlEscape($descript).
    '</td>');
  printf('</tr>');
}
?>
  </tbody>
</table>
<table>
<script type='text/javascript'>
function show(id) {
  document.getElementById(id).style="display:inline;";
}
function hide(id) {
  document.getElementById(id).style="display:none;";
}
function showAdd() {
  show("add");
  hide("edit");
  hide("delete");
}
function showEdit() {
  hide("add");
  show("edit");
  hide("delete");
}
function showDelete() {
  hide("add");
  hide("edit");
  show("delete");
}
function colNameUpdated(value) {
  if(value=="due date") {
    document.getElementById('editText').type='datetime-local';
  } else if(value=="title"
    || value=="status"
    || value=="details") {
    document.getElementById('editText').type='text';
  }
}
</script>
<br>
<tr>
  <td>
    <a class='button' onclick='showAdd()'>
    Add
    </a>
  </td>
  <td>
    <a class='button' onclick='showEdit()'>
    Edit
    </a>
  </td>
  <td>
    <a class='button' onclick='showDelete()'>
    Delete
    </a>
  </td>
  <td>
    <a class='button' onclick='document.forms["refreshform"].submit();'>
    Refresh
    </a>
    <form name='refreshform'
          action="helloworld.php"
          method="get"
          style='display:none;'>
      <input type="submit"
        value="Refresh">
      <input type="text"
        name="name"
        value=<?php echo "'".$ownerName."'"?>>
    </form>
  </td>
</tr>
</table>
<br>
<div id="add" style='display:none;'>
<form action="helloworld.php" method="get">
  Username:
  <input type="text" name="name" value=<?php echo "'".$ownerName."'" ?>><br>
  <input style='display:none;'
    type="text" name="command" value="add">
  Title:
  <input type="text" name="title"><br>
  Status:
  <input type="text" name="status"><br>
  Due date:
  <input type="datetime-local" name="duedate"><br>
  Details:
  <input type="text" name="details"><br>
  <input type="submit" value="Submit">
</form>
</div>
<div id="edit" style='display:none;'>
<!--this should be http POST, but for technical reasons it's easier to just use GET-->
<form action="helloworld.php" method="get">
  Username:
  <input type="text" name="name" value=<?php echo "'".$ownerName."'" ?>><br>
  <input style='display:none;'
    type="text" name="command" value="edit">
  Row number:
  <input type="text" name="rowNum"><br>
  Column name:
  <input type="text" name="colName" oninput='colNameUpdated(this.value)'><br>
  New value:
  <input id='editText'
    type="text"
    name="value">
  <br>
  <input type="submit" value="Submit">
</form>
</div>
<div id="delete" style='display:none;'>
<!--this should be http POST, but for technical reasons it's easier to just use GET-->
<form action="helloworld.php" method="get">
  Username:
  <input type="text" name="name" value=<?php echo "'".$ownerName."'" ?>><br>
  <input style='display:none;'
    type="text" name="command" value="delete">
  Row number:
  <input type="text" name="rownumber"><br>
  <input type="submit" value="Submit">
</div>
<?php mysqli_close($sql) ?>