<?php

namespace Rector\Tests\DeadCode\Rector\ClassMethod\RemoveEmptyClassMethodRector\Fixture;

use Rector\Tests\DeadCode\Rector\ClassMethod\RemoveEmptyClassMethodRector\Source\MyPdo;

/**
 * Construct empty with extends can be used for stub in test
 */
class SkipConstructClassExtends extends MyPdo
{
    public function __construct()
    {
    }
}

?>
