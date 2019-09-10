<?php declare(strict_types=1);

namespace Rector\DoctrinePhpDocParser\Contract\Ast\PhpDoc;

interface InversedByNodeInterface
{
    public function getInversedBy(): ?string;

    public function removeInversedBy(): void;
}
