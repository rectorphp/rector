<?php

declare (strict_types=1);
namespace Rector\Transform\NodeFactory;

use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\Variable;
use PHPStan\Type\ObjectType;
use Rector\Naming\Naming\PropertyNaming;
final class PropertyFetchFactory
{
    /**
     * @var \Rector\Naming\Naming\PropertyNaming
     */
    private $propertyNaming;
    public function __construct(\Rector\Naming\Naming\PropertyNaming $propertyNaming)
    {
        $this->propertyNaming = $propertyNaming;
    }
    public function createFromType(\PHPStan\Type\ObjectType $objectType) : \PhpParser\Node\Expr\PropertyFetch
    {
        $thisVariable = new \PhpParser\Node\Expr\Variable('this');
        $propertyName = $this->propertyNaming->fqnToVariableName($objectType->getClassName());
        return new \PhpParser\Node\Expr\PropertyFetch($thisVariable, $propertyName);
    }
}
