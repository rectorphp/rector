<?php declare(strict_types=1);

namespace Rector\NodeTypeResolver\PerNodeTypeResolver;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassLike;
use Rector\NodeAnalyzer\ClassAnalyzer;
use Rector\NodeTypeResolver\Contract\PerNodeTypeResolver\PerNodeTypeResolverInterface;
use Rector\NodeTypeResolver\TypeContext;

final class ClassLikeTypeResolver implements PerNodeTypeResolverInterface
{
    /**
     * @var ClassAnalyzer
     */
    private $classAnalyzer;

    public function __construct(TypeContext $typeContext, ClassAnalyzer $classAnalyzer)
    {
        $this->classAnalyzer = $classAnalyzer;
    }

    public function getNodeClass(): string
    {
        return ClassLike::class;
    }

    /**
     * @param ClassLike $classLikeNode
     */
    public function resolve(Node $classLikeNode): ?string
    {
        if ($classLikeNode instanceof Class_) {
            $parentTypes = $this->classAnalyzer->resolveParentTypes($classLikeNode);
            // @todo: add support for many-types later

            if (! count($parentTypes)) {
                return null;
            }
        }

        return null;
    }
}
