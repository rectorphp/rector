<?php

namespace Rector\Tests\Naming\Rector\Class_\RenamePropertyToMatchTypeRector\FixturePhp80;

use Rector\Core\NodeAnalyzer\ClassAnalyzer;

final class SkipChangeDuplicateType
{
    public function __construct(
        private ClassAnalyzer $classAnalyzer1,
        private ClassAnalyzer $classAnalyzer2
    ) {
    }
}
