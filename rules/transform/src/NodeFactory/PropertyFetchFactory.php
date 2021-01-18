<?php

declare(strict_types=1);

namespace Rector\Transform\NodeFactory;

use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\Variable;
use Rector\Naming\Naming\PropertyNaming;

final class PropertyFetchFactory
{
    /**
     * @var PropertyNaming
     */
    private $propertyNaming;

    public function __construct(PropertyNaming $propertyNaming)
    {
        $this->propertyNaming = $propertyNaming;
    }

    public function createFromType(string $type): PropertyFetch
    {
        $thisVariable = new Variable('this');
        $propertyName = $this->propertyNaming->fqnToVariableName($type);

        return new PropertyFetch($thisVariable, $propertyName);
    }
}
