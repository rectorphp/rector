<?php

declare(strict_types=1);

use Rector\NodeDumper\NodeDumper;

if (! function_exists('rd')) {
    function rd($var): void
    {
        NodeDumper::dumpNode($var);
    }
}

if (! function_exists('rdd')) {
    function rdd($var): void
    {
        rd($var);
        die;
    }
}
