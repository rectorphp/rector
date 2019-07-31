<?php declare(strict_types=1);

namespace Rector\TypeDeclaration\Contract;

use PhpParser\Node\Stmt\Property;

interface PropertyTypeInfererInterface
{
    /**
     * @return string[]
     */
    public function inferProperty(Property $property): array;

    /**
     * Higher priority goes first.
     */
    public function getPriority(): int;
}
