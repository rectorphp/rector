<?php

declare(strict_types=1);

namespace Rector\Naming\ExpectedNameResolver;

use Nette\Utils\Strings;
use PhpParser\Node\Stmt\Property;
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

    public function resolveIfNotYet(Property $property): ?string
    {
        $expectedName = $this->resolve($property);
        if ($expectedName === null) {
            return null;
        }

        /** @var string $propertyName */
        $propertyName = $this->nodeNameResolver->getName($property);
        if ($this->endsWith($propertyName, $expectedName)) {
            return null;
        }

        if ($this->nodeNameResolver->isName($property, $expectedName)) {
            return null;
        }

        return $expectedName;
    }

    /**
     * Ends with ucname
     * Starts with adjective, e.g. (Post $firstPost, Post $secondPost)
     */
    protected function endsWith(string $currentName, string $expectedName): bool
    {
        $suffixNamePattern = '#\w+' . ucfirst($expectedName) . '#';
        return (bool) Strings::match($currentName, $suffixNamePattern);
    }
}
