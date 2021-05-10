<?php

declare(strict_types=1);

namespace Rector\Transform\NodeFactory;

use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\Variable;
use PHPStan\Type\ObjectType;
use Rector\Naming\Naming\PropertyNaming;

final class PropertyFetchFactory
{
    public function __construct(
        private PropertyNaming $propertyNaming
    ) {
    }

    public function createFromType(ObjectType $objectType): PropertyFetch
    {
        $thisVariable = new Variable('this');
        $propertyName = $this->propertyNaming->fqnToVariableName($objectType->getClassName());

        return new PropertyFetch($thisVariable, $propertyName);
    }
}
