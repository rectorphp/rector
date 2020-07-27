<?php

declare(strict_types=1);

namespace Rector\Decouple\Matcher;

use PhpParser\Node\Stmt\ClassMethod;
use Rector\Decouple\ValueObject\DecoupleClassMethodMatch;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\NodeTypeResolver\NodeTypeResolver;

final class DecoupledClassMethodMatcher
{
    /**
     * @var NodeNameResolver
     */
    private $nodeNameResolver;

    /**
     * @var NodeTypeResolver
     */
    private $nodeTypeResolver;

    public function __construct(NodeNameResolver $nodeNameResolver, NodeTypeResolver $nodeTypeResolver)
    {
        $this->nodeNameResolver = $nodeNameResolver;
        $this->nodeTypeResolver = $nodeTypeResolver;
    }

    public function matchDecoupled(ClassMethod $classMethod, array $methodNamesByClass): ?DecoupleClassMethodMatch
    {
        $classLike = $classMethod->getAttribute(AttributeKey::CLASS_NODE);
        if ($classLike === null) {
            return null;
        }

        foreach ($methodNamesByClass as $className => $configuration) {
            if (! $this->nodeTypeResolver->isObjectType($classLike, $className)) {
                continue;
            }

            foreach ($configuration as $methodName => $newClassConfiguration) {
                if (! $this->nodeNameResolver->isName($classMethod->name, $methodName)) {
                    continue;
                }

                return new DecoupleClassMethodMatch(
                    $newClassConfiguration['class'],
                    $newClassConfiguration['method'],
                    $newClassConfiguration['parent_class'] ?? null
                );
            }
        }

        return null;
    }
}
