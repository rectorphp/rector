<?php

namespace Rector\Php\Tests\Rector\FuncCall\CountOnNullRector\Integration;

function a(array $a = [])
{
    count($a);
}

/**
 * @param array $b
 */
function b($b = [])
{
    count($b);
}

/**
 * @param array $c
 */
function c($c)
{
    count($c);
}

/**
 * @param mixed[] $d
 */
function d($d)
{
    count($d);
}

/**
 * @param string[] $e
 */
function e($e)
{
    count($e);
}
