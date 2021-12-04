<?php

declare(strict_types=1);

namespace Rector\Tests\TypeDeclaration\Rector\ClassMethod\ReturnNeverTypeRector\Source;

use PhpParser\Node;

interface RunnableInterface
{
    public function run(): ?Node;
}
