<?php

declare(strict_types=1);

namespace Rector\BetterPhpDocParser\Contract\PhpDocNode;

use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagValueNode;

interface ClassNameAwareTagInterface extends PhpDocTagValueNode
{
    public function getClassName(): string;
}
