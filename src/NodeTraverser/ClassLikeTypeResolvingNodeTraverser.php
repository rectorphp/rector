<?php declare(strict_types=1);

namespace Rector\NodeTraverser;

use PhpParser\NodeTraverser;
use Rector\NodeTypeResolver\NodeVisitor\ClassLikeTypeResolver;

final class ClassLikeTypeResolvingNodeTraverser extends NodeTraverser
{
    public function __construct(ClassLikeTypeResolver $classLikeTypeResolver)
    {
        $this->addVisitor($classLikeTypeResolver);
    }
}
