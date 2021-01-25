<?php

declare(strict_types=1);

namespace Rector\PhpAttribute\Contract;

use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagValueNode;

interface PhpAttributableTagNodeInterface extends PhpDocTagValueNode
{
    public function getShortName(): string;

    public function getAttributeClassName(): string;

    /**
     * @return mixed[]
     */
    public function getAttributableItems(): array;
}
