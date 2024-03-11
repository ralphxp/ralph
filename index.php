<?php
namespace Bird;

include __DIR__ . "/vendor/autoload.php";

use \Bird\Ralph\Engine as View;
$title = "Home page";
View::view("home", compact('title'));