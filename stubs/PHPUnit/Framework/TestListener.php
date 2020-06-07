<?php

declare(strict_types=1);

namespace PHPUnit\Framework;

if (interface_exists('PHPUnit\Framework\TestListener')) {
    return;
}

interface TestListener
{

}
