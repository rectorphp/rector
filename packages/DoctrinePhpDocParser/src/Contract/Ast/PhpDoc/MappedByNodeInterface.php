<?php declare(strict_types=1);

namespace Rector\DoctrinePhpDocParser\Contract\Ast\PhpDoc;

interface MappedByNodeInterface
{
    public function getMappedBy(): ?string;

    public function removeMappedBy(): void;
}
