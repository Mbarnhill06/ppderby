<?php @session_start(); ?>
<?php
require_once('data.inc');
require_once('permissions.inc');
require_once('roles.inc');

if (isset($_POST['name'])) {
  $name = $_POST['name'];
  $password = $_POST['password'];
} else {
  $name = $_GET['name'];
  $password = $_GET['password'];
}


header('Content-Type: text/xml');

echo '<login-action>'."\n";

$role = $roles[$name];

if ($role) {
  if ($password == $role['password']) {
    $_SESSION['permissions'] = $role['permissions'];
    if ($password)
      $_SESSION['role'] = $name;
    else
      unset($_SESSION['role']);
    echo '<success>'.$_SESSION['role'].'</success>'."\n";
  } else {
    echo '<failure>Incorrect password</failure>'."\n";
  }
} else {
  echo '<failure role="'.$name.'">No such role: '.$name.'</failure>'."\n";
}
echo '</login-action>'."\n";
?>