<?php

declare(strict_types=1);

namespace Rector\Tests\DeadCode\Rector\ClassMethod\RemoveEmptyClassMethodRector\Source;

class MyPdo
{
    public function __construct()
    {
        db_connnect();
    }
}