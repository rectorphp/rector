<?php

declare (strict_types=1);
namespace Rector\DeadCode\NodeManipulator;

use PhpParser\Node\Name;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PHPStan\PhpDocParser\Ast\PhpDoc\GenericTagValueNode;
use Rector\BetterPhpDocParser\PhpDoc\SpacelessPhpDocTagNode;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory;
use Rector\NodeNameResolver\NodeNameResolver;
final class ControllerClassMethodManipulator
{
    /**
     * @readonly
     */
    private NodeNameResolver $nodeNameResolver;
    /**
     * @readonly
     */
    private PhpDocInfoFactory $phpDocInfoFactory;
    public function __construct(NodeNameResolver $nodeNameResolver, PhpDocInfoFactory $phpDocInfoFactory)
    {
        $this->nodeNameResolver = $nodeNameResolver;
        $this->phpDocInfoFactory = $phpDocInfoFactory;
    }
    public function isControllerClassMethod(Class_ $class, ClassMethod $classMethod) : bool
    {
        if (!$classMethod->isPublic()) {
            return \false;
        }
        if (!$this->hasParentClassController($class)) {
            return \false;
        }
        $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($classMethod);
        return $phpDocInfo->hasByTypes([GenericTagValueNode::class, SpacelessPhpDocTagNode::class]);
    }
    private function hasParentClassController(Class_ $class) : bool
    {
        if (!$class->extends instanceof Name) {
            return \false;
        }
        $parentClassName = $this->nodeNameResolver->getName($class->extends);
        if (\substr_compare($parentClassName, 'Controller', -\strlen('Controller')) === 0) {
            return \true;
        }
        return \substr_compare($parentClassName, 'Presenter', -\strlen('Presenter')) === 0;
    }
}
