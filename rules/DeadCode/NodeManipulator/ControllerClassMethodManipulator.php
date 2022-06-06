<?php

declare (strict_types=1);
namespace Rector\DeadCode\NodeManipulator;

use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PHPStan\PhpDocParser\Ast\PhpDoc\GenericTagValueNode;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory;
use Rector\Core\PhpParser\Node\BetterNodeFinder;
use Rector\NodeNameResolver\NodeNameResolver;
final class ControllerClassMethodManipulator
{
    /**
     * @readonly
     * @var \Rector\NodeNameResolver\NodeNameResolver
     */
    private $nodeNameResolver;
    /**
     * @readonly
     * @var \Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory
     */
    private $phpDocInfoFactory;
    /**
     * @readonly
     * @var \Rector\Core\PhpParser\Node\BetterNodeFinder
     */
    private $betterNodeFinder;
    public function __construct(\Rector\NodeNameResolver\NodeNameResolver $nodeNameResolver, \Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory $phpDocInfoFactory, \Rector\Core\PhpParser\Node\BetterNodeFinder $betterNodeFinder)
    {
        $this->nodeNameResolver = $nodeNameResolver;
        $this->phpDocInfoFactory = $phpDocInfoFactory;
        $this->betterNodeFinder = $betterNodeFinder;
    }
    public function isControllerClassMethodWithBehaviorAnnotation(\PhpParser\Node\Stmt\ClassMethod $classMethod) : bool
    {
        if (!$this->isControllerClassMethod($classMethod)) {
            return \false;
        }
        $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($classMethod);
        return $phpDocInfo->hasByType(\PHPStan\PhpDocParser\Ast\PhpDoc\GenericTagValueNode::class);
    }
    private function isControllerClassMethod(\PhpParser\Node\Stmt\ClassMethod $classMethod) : bool
    {
        if (!$classMethod->isPublic()) {
            return \false;
        }
        $class = $this->betterNodeFinder->findParentType($classMethod, \PhpParser\Node\Stmt\Class_::class);
        if (!$class instanceof \PhpParser\Node\Stmt\Class_) {
            return \false;
        }
        return $this->hasParentClassController($class);
    }
    private function hasParentClassController(\PhpParser\Node\Stmt\Class_ $class) : bool
    {
        if ($class->extends === null) {
            return \false;
        }
        return $this->nodeNameResolver->isName($class->extends, '#(Controller|Presenter)$#');
    }
}
