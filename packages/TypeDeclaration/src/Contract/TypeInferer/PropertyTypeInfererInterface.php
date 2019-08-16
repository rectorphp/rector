<?php declare(strict_types=1);

namespace Rector\TypeDeclaration\Contract\TypeInferer;

use PhpParser\Node\Stmt\Property;
use Rector\TypeDeclaration\ValueObject\IdentifierValueObject;

interface PropertyTypeInfererInterface
{
    /**
     * @return string[]|IdentifierValueObject[]
     */
    public function inferProperty(Property $property): array;

    /**
     * Higher priority goes first.
     */
    public function getPriority(): int;
}
