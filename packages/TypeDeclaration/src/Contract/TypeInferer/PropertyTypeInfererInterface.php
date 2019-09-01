<?php declare(strict_types=1);

namespace Rector\TypeDeclaration\Contract\TypeInferer;

use PhpParser\Node\Stmt\Property;
use Rector\TypeDeclaration\ValueObject\IdentifierValueObject;

interface PropertyTypeInfererInterface extends PriorityAwareTypeInfererInterface
{
    /**
     * @return string[]|IdentifierValueObject[]
     */
    public function inferProperty(Property $property): array;
}
