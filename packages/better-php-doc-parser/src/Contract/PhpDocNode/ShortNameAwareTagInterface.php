<?php

declare(strict_types=1);

namespace Rector\BetterPhpDocParser\Contract\PhpDocNode;

use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagValueNode;

interface ShortNameAwareTagInterface extends PhpDocTagValueNode
{
    public function getShortName(): string;
}
