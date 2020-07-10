<?php
if (isset($_POST['csrf'])) {
processPost($_POST);
}
-----
<?php

include 'autoloader.php';

if (isset($_POST['csrf'])) {
processPost($_POST);
}
