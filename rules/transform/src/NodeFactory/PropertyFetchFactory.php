<?php

declare(strict_types=1);

namespace Rector\Transform\NodeFactory;

use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\Variable;
use Rector\Naming\Naming\PropertyNaming;
use Rector\NodeNameResolver\NodeNameResolver;

final class PropertyFetchFactory
{
    /**
     * @var PropertyNaming
     */
    private $propertyNaming;

    /**
     * @var NodeNameResolver
     */
    private $nodeNameResolver;

    public function __construct(PropertyNaming $propertyNaming, NodeNameResolver $nodeNameResolver)
    {
        $this->propertyNaming = $propertyNaming;
        $this->nodeNameResolver = $nodeNameResolver;
    }

    public function createFromType(string $type): PropertyFetch
    {
        $thisVariable = new Variable('this');
        $propertyName = $this->propertyNaming->fqnToVariableName($type);

        return new PropertyFetch($thisVariable, $propertyName);
    }
}
