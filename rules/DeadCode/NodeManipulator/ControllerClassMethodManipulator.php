<?php

declare(strict_types=1);

namespace Rector\DeadCode\NodeManipulator;

use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PHPStan\PhpDocParser\Ast\PhpDoc\GenericTagValueNode;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory;
use Rector\Core\PhpParser\Node\BetterNodeFinder;
use Rector\NodeNameResolver\NodeNameResolver;

final class ControllerClassMethodManipulator
{
    public function __construct(
        private readonly NodeNameResolver $nodeNameResolver,
        private readonly PhpDocInfoFactory $phpDocInfoFactory,
        private readonly BetterNodeFinder $betterNodeFinder,
    ) {
    }

    public function isControllerClassMethodWithBehaviorAnnotation(ClassMethod $classMethod): bool
    {
        if (! $this->isControllerClassMethod($classMethod)) {
            return false;
        }

        $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($classMethod);
        return $phpDocInfo->hasByType(GenericTagValueNode::class);
    }

    private function isControllerClassMethod(ClassMethod $classMethod): bool
    {
        if (! $classMethod->isPublic()) {
            return false;
        }

        $class = $this->betterNodeFinder->findParentType($classMethod, Class_::class);
        if (! $class instanceof Class_) {
            return false;
        }

        return $this->hasParentClassController($class);
    }

    private function hasParentClassController(Class_ $class): bool
    {
        if ($class->extends === null) {
            return false;
        }

        return $this->nodeNameResolver->isName($class->extends, '#(Controller|Presenter)$#');
    }
}
