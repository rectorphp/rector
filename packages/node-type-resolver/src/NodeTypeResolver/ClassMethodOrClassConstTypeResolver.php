<?php

declare(strict_types=1);

namespace Rector\NodeTypeResolver\NodeTypeResolver;

use PhpParser\Node;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\ClassConst;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\Node\Stmt\ClassMethod;
use PHPStan\Type\ObjectWithoutClassType;
use PHPStan\Type\Type;
use Rector\NodeTypeResolver\Contract\NodeTypeResolverInterface;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\NodeTypeResolver\NodeTypeResolver;

final class ClassMethodOrClassConstTypeResolver implements NodeTypeResolverInterface
{
    /**
     * @var NodeTypeResolver
     */
    private $nodeTypeResolver;

    /**
     * @required
     */
    public function autowireClassMethodOrClassConstTypeResolver(NodeTypeResolver $nodeTypeResolver): void
    {
        $this->nodeTypeResolver = $nodeTypeResolver;
    }

    /**
     * @return string[]
     */
    public function getNodeClasses(): array
    {
        return [ClassMethod::class, ClassConst::class];
    }

    /**
     * @param ClassMethod|ClassConst $node
     */
    public function resolve(Node $node): Type
    {
        $classLike = $node->getAttribute(AttributeKey::CLASS_NODE);
        if (! $classLike instanceof ClassLike) {
            // anonymous class
            return new ObjectWithoutClassType();
        }

        return $this->nodeTypeResolver->resolve($classLike);
    }
}
