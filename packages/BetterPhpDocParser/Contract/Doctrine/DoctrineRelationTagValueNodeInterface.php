<?php

declare(strict_types=1);

namespace Rector\BetterPhpDocParser\Contract\Doctrine;

use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagValueNode;

interface DoctrineRelationTagValueNodeInterface extends PhpDocTagValueNode
{
    public function getTargetEntity(): ?string;

    public function getFullyQualifiedTargetEntity(): ?string;

    public function changeTargetEntity(string $targetEntity): void;
}
