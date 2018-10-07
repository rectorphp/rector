<?php declare(strict_types=1);

$obj = new stdClass();
$method_name = 'hi';

call_user_func_array([$obj, $method_name], $params);

call_user_func([$obj, $method_name], $params);
