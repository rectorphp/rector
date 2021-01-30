<?php

declare(strict_types=1);

namespace Rector\PhpAttribute\Contract;

use PHPStan\PhpDocParser\Ast\Node;

interface PhpAttributableTagNodeInterface extends Node
{
    public function getShortName(): string;

    public function getAttributeClassName(): string;

    /**
     * @return mixed[]
     */
    public function getAttributableItems(): array;
}
