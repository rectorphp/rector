<?php

declare(strict_types=1);

namespace Rector\Tests\TypeDeclaration\Rector\ClassMethod\AddParamTypeFromCallersRector\Source;

use PhpParser\Node;

interface SomeInterface
{
    public function print(Node $node);
}
