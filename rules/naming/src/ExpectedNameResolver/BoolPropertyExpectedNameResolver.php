<?php

declare(strict_types=1);

namespace Rector\Naming\ExpectedNameResolver;

use PhpParser\Node;
use PhpParser\Node\Stmt\Property;
use Rector\Naming\Naming\PropertyNaming;

final class BoolPropertyExpectedNameResolver extends AbstractExpectedNameResolver
{
    /**
     * @var PropertyNaming
     */
    private $propertyNaming;

    /**
     * @required
     */
    public function autowireBoolPropertyExpectedNameResolver(PropertyNaming $propertyNaming): void
    {
        $this->propertyNaming = $propertyNaming;
    }

    /**
     * @param Property $node
     */
    public function resolve(Node $node): ?string
    {
        if (! $this->nodeTypeResolver->isPropertyBoolean($node)) {
            return null;
        }

        return $this->propertyNaming->getExpectedNameFromBooleanPropertyType($node);
    }
}
