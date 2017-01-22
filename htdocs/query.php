<?php
function sqlEscape($str) {
  global $sql;
  return mysqli_real_escape_string($sql, $str);
}

function htmlEscape($str) {
  return htmlspecialchars($str);
}

function printHtml($str) {
  print(htmlEscape($str));
}

function printSql($str) {
  print(sqlEscape($str));
}

function noop(...$args) {}

$servername = "localhost";
$db = "tasks";
$sql = mysqli_connect($servername, "root", "fk8034", $db);
if(mysqli_connect_errno()) {
  die("Connection failed: ".mysqli_connect_error());
}

function query($query, $fn='noop', $onBadNews='noop')
{
  global $sql;
  if($result = mysqli_query($sql, $query)) {
    if($result !== True) {
      while ($row = mysqli_fetch_row($result)) {
        call_user_func($fn, $row);
      }
    }
  } else {
    call_user_func($onBadNews);
  }
}
?>