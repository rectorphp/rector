<?php

declare (strict_types=1);
namespace RectorPrefix20211020;

// mimic missing T_ENUM constant on PHP 8.0-
if (!\defined('T_ENUM')) {
    \define('T_ENUM', 5000);
}
// mimic missing T_NAME_RELATIVE constant on PHP 8.0-
if (!\defined('T_NAME_RELATIVE')) {
    \define('T_NAME_RELATIVE', 5001);
}
