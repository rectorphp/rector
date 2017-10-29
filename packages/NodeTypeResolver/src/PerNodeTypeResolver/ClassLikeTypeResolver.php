<?php declare(strict_types=1);

namespace Rector\NodeTypeResolver\PerNodeTypeResolver;

use PhpParser\Node;
use PhpParser\Node\Stmt\ClassLike;
use Rector\Node\Attribute;
use Rector\NodeAnalyzer\ClassAnalyzer;
use Rector\NodeTypeResolver\Contract\PerNodeTypeResolver\PerNodeTypeResolverInterface;

final class ClassLikeTypeResolver implements PerNodeTypeResolverInterface
{
    /**
     * @var ClassAnalyzer
     */
    private $classAnalyzer;

    public function __construct(ClassAnalyzer $classAnalyzer)
    {
        $this->classAnalyzer = $classAnalyzer;
    }

    public function getNodeClass(): string
    {
        return ClassLike::class;
    }

    /**
     * @param ClassLike $classLikeNode
     * @return string[]
     */
    public function resolve(Node $classLikeNode): array
    {
        $types = $this->classAnalyzer->resolveTypeAndParentTypes($classLikeNode);

        if (! $types) {
            return [];
        }

        $classLikeNode->setAttribute(Attribute::TYPES, $types);

        return $types;
    }
}
