<?php
require 'config.php';


if (!empty($_POST['name'])) $name = $_POST['name'];
else if (!empty($_GET['name'])) $name = $_GET['name'];
else die();

#print_r($name);

getPoster($name); // in functions.php
?>