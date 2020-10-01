<?php

declare(strict_types=1);

namespace Rector\Restoration\NameMatcher;

use Nette\Utils\Strings;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\NullableType;
use PhpParser\Node\UnionType;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\NodeTypeResolver\ClassExistenceStaticHelper;
use Rector\Restoration\ClassMap\ExistingClassesProvider;

final class FullyQualifiedNameMatcher
{
    /**
     * @var ExistingClassesProvider
     */
    private $existingClassesProvider;

    /**
     * @var NodeNameResolver
     */
    private $nodeNameResolver;

    public function __construct(ExistingClassesProvider $existingClassesProvider, NodeNameResolver $nodeNameResolver)
    {
        $this->existingClassesProvider = $existingClassesProvider;
        $this->nodeNameResolver = $nodeNameResolver;
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
            if ($resolvedName === null) {
                return null;
            }

            if (ClassExistenceStaticHelper::doesClassLikeExist($resolvedName)) {
                return null;
            }

            return $this->makeNodeFullyQualified($resolvedName);
        }

        return null;
    }

    private function makeNodeFullyQualified(string $desiredShortName): ?FullyQualified
    {
        foreach ($this->existingClassesProvider->provide() as $declaredClass) {
            $declaredShortClass = (string) Strings::after($declaredClass, '\\', -1);
            if ($declaredShortClass !== $desiredShortName) {
                continue;
            }

            return new FullyQualified($declaredClass);
        }

        return null;
    }
}
