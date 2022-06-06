<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Rector\Transform\NodeFactory;

use RectorPrefix20220606\PhpParser\Node\Expr\PropertyFetch;
use RectorPrefix20220606\PhpParser\Node\Expr\Variable;
use RectorPrefix20220606\PHPStan\Type\ObjectType;
use RectorPrefix20220606\Rector\Naming\Naming\PropertyNaming;
final class PropertyFetchFactory
{
    /**
     * @readonly
     * @var \Rector\Naming\Naming\PropertyNaming
     */
    private $propertyNaming;
    public function __construct(PropertyNaming $propertyNaming)
    {
        $this->propertyNaming = $propertyNaming;
    }
    public function createFromType(ObjectType $objectType) : PropertyFetch
    {
        $thisVariable = new Variable('this');
        $propertyName = $this->propertyNaming->fqnToVariableName($objectType->getClassName());
        return new PropertyFetch($thisVariable, $propertyName);
    }
}
