<?php declare(strict_types=1);

namespace Rector\DoctrinePhpDocParser\Contract\Ast\PhpDoc;

interface RelationTagValueNodeInterface
{
    public function getTargetEntity(): ?string;

    public function getFqnTargetEntity(): ?string;
}
