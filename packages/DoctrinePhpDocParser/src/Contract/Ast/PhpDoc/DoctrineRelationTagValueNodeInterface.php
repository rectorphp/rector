<?php declare(strict_types=1);

namespace Rector\DoctrinePhpDocParser\Contract\Ast\PhpDoc;

interface DoctrineRelationTagValueNodeInterface
{
    public function getTargetEntity(): ?string;

    public function getFqnTargetEntity(): ?string;

    public function changeTargetEntity(string $targetEntity): void;
}
