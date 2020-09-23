<?php

declare(strict_types=1);

namespace Rector\Naming\ExpectedNameResolver;

use Rector\Naming\Naming\PropertyNaming;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\NodeTypeResolver\NodeTypeResolver;

abstract class AbstractExpectedNameResolver implements ExpectedNameResolverInterface
{
    /**
     * @var NodeTypeResolver
     */
    protected $nodeTypeResolver;

    /**
     * @var PropertyNaming
     */
    protected $propertyNaming;

    /**
     * @var NodeNameResolver
     */
    protected $nodeNameResolver;

    public function __construct(
        NodeNameResolver $nodeNameResolver,
        NodeTypeResolver $nodeTypeResolver,
        PropertyNaming $propertyNaming
    ) {
        $this->nodeNameResolver = $nodeNameResolver;
        $this->nodeTypeResolver = $nodeTypeResolver;
        $this->propertyNaming = $propertyNaming;
    }
}
