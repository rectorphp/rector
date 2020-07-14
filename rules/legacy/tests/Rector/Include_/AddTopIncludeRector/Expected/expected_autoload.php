<?php

require_once __DIR__ . '/../autoloader.php';

if (isset($_POST['csrf'])) {
    processPost($_POST);
}
