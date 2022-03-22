<?php

declare(strict_types=1);

namespace PHPUnit\Framework;

if (class_exists('PHPUnit\Framework\TestCase')) {
    return;
}

abstract class TestCase
{
}
