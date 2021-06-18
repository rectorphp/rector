<?php

namespace Rector\Tests\Naming\Rector\Class_\RenamePropertyToMatchTypeRector\Fixture;

use PhpParser\Node;

final class SkipMultipleSameTypes
{
    public function __construct(
        private Node $variable,
        private Node $assign
    ) {
    }
}
