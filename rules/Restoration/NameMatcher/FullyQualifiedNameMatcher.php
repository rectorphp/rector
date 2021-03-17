<?php

declare(strict_types=1);

namespace Rector\Restoration\NameMatcher;

use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\NullableType;
use PhpParser\Node\UnionType;
use PHPStan\Reflection\ReflectionProvider;
use Rector\NodeNameResolver\NodeNameResolver;

final class FullyQualifiedNameMatcher
{
    /**
     * @var NodeNameResolver
     */
    private $nodeNameResolver;

    /**
     * @var NameMatcher
     */
    private $nameMatcher;

    /**
     * @var ReflectionProvider
     */
    private $reflectionProvider;

    public function __construct(
        NodeNameResolver $nodeNameResolver,
        NameMatcher $nameMatcher,
        ReflectionProvider $reflectionProvider
    ) {
        $this->nodeNameResolver = $nodeNameResolver;
        $this->nameMatcher = $nameMatcher;
        $this->reflectionProvider = $reflectionProvider;
    }

    /**
     * @param string|Name|Identifier|FullyQualified|UnionType|NullableType|null $name
     * @return NullableType|FullyQualified|null
     */
    public function matchFullyQualifiedName($name)
    {
        if ($name instanceof NullableType) {
            $fullyQulifiedNullableType = $this->matchFullyQualifiedName($name->type);
            if (! $fullyQulifiedNullableType instanceof Name) {
                return null;
            }

            return new NullableType($fullyQulifiedNullableType);
        }

        if ($name instanceof Name) {
            if (count($name->parts) !== 1) {
                return null;
            }

            $resolvedName = $this->nodeNameResolver->getName($name);
            if ($this->reflectionProvider->hasClass($resolvedName)) {
                return null;
            }

            $fullyQualified = $this->nameMatcher->makeNameFullyQualified($resolvedName);
            if ($fullyQualified === null) {
                return null;
            }

            return new FullyQualified($fullyQualified);
        }

        return null;
    }
}
