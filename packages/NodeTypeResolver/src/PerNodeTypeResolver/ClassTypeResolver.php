<?php declare(strict_types=1);

namespace Rector\NodeTypeResolver\PerNodeTypeResolver;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassLike;
use Rector\NodeAnalyzer\ClassLikeAnalyzer;
use Rector\NodeTypeResolver\Contract\PerNodeTypeResolver\PerNodeTypeResolverInterface;

final class ClassTypeResolver implements PerNodeTypeResolverInterface
{
    /**
     * @var ClassLikeAnalyzer
     */
    private $classLikeAnalyzer;

    public function __construct(ClassLikeAnalyzer $classLikeAnalyzer)
    {
        $this->classLikeAnalyzer = $classLikeAnalyzer;
    }

    /**
     * @return string[]
     */
    public function getNodeClasses(): array
    {
        return [Class_::class];
    }

    /**
     * @param ClassLike $classLikeNode
     * @return string[]
     */
    public function resolve(Node $classLikeNode): array
    {
        return $this->classLikeAnalyzer->resolveTypeAndParentTypes($classLikeNode);
    }
}
