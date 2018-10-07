<?php declare(strict_types=1);

$obj = new stdClass();
$method_name = 'hi';

call_user_method_array($method_name, $obj, $params);

call_user_method($method_name, $obj, $params);
