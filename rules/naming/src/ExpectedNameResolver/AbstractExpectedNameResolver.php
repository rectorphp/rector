<?php

declare(strict_types=1);

namespace Rector\Naming\ExpectedNameResolver;

use Nette\Utils\Strings;
use PhpParser\Node;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\NodeTypeResolver\NodeTypeResolver;

abstract class AbstractExpectedNameResolver implements ExpectedNameResolverInterface
{
    /**
     * @var NodeTypeResolver
     */
    protected $nodeTypeResolver;

    /**
     * @var NodeNameResolver
     */
    protected $nodeNameResolver;

    public function __construct(NodeNameResolver $nodeNameResolver, NodeTypeResolver $nodeTypeResolver)
    {
        $this->nodeNameResolver = $nodeNameResolver;
        $this->nodeTypeResolver = $nodeTypeResolver;
    }

    public function resolveIfNotYet(Node $node): ?string
    {
        $expectedName = $this->resolve($node);
        if ($expectedName === null) {
            return null;
        }

        /** @var string $currentName */
        $currentName = $this->nodeNameResolver->getName($node);

        if ($this->endsWith($currentName, $expectedName)) {
            return null;
        }

        if ($this->nodeNameResolver->isName($node, $expectedName)) {
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
