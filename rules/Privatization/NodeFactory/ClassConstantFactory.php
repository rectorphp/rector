<?php

declare (strict_types=1);
namespace Rector\Privatization\NodeFactory;

use PhpParser\Node\Const_;
use PhpParser\Node\Expr;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassConst;
use PhpParser\Node\Stmt\Property;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\Privatization\Naming\ConstantNaming;
final class ClassConstantFactory
{
    /**
     * @var \Rector\Privatization\Naming\ConstantNaming
     */
    private $constantNaming;
    /**
     * @var \Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory
     */
    private $phpDocInfoFactory;
    public function __construct(\Rector\Privatization\Naming\ConstantNaming $constantNaming, \Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory $phpDocInfoFactory)
    {
        $this->constantNaming = $constantNaming;
        $this->phpDocInfoFactory = $phpDocInfoFactory;
    }
    public function createFromProperty(\PhpParser\Node\Stmt\Property $property) : \PhpParser\Node\Stmt\ClassConst
    {
        $propertyProperty = $property->props[0];
        $constantName = $this->constantNaming->createFromProperty($propertyProperty);
        /** @var Expr $defaultValue */
        $defaultValue = $propertyProperty->default;
        $const = new \PhpParser\Node\Const_($constantName, $defaultValue);
        $classConst = new \PhpParser\Node\Stmt\ClassConst([$const]);
        $classConst->flags = $property->flags & ~\PhpParser\Node\Stmt\Class_::MODIFIER_STATIC;
        $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($property);
        $phpDocInfo->markAsChanged();
        $classConst->setAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::PHP_DOC_INFO, $phpDocInfo);
        return $classConst;
    }
}
