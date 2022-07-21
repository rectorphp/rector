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
     * @readonly
     * @var \Rector\Privatization\Naming\ConstantNaming
     */
    private $constantNaming;
    /**
     * @readonly
     * @var \Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory
     */
    private $phpDocInfoFactory;
    public function __construct(ConstantNaming $constantNaming, PhpDocInfoFactory $phpDocInfoFactory)
    {
        $this->constantNaming = $constantNaming;
        $this->phpDocInfoFactory = $phpDocInfoFactory;
    }
    public function createFromProperty(Property $property) : ClassConst
    {
        $propertyProperty = $property->props[0];
        $constantName = $this->constantNaming->createFromProperty($propertyProperty);
        /** @var Expr $defaultValue */
        $defaultValue = $propertyProperty->default;
        $const = new Const_($constantName, $defaultValue);
        $classConst = new ClassConst([$const]);
        $classConst->flags = $property->flags & ~Class_::MODIFIER_STATIC;
        $const->setAttribute(AttributeKey::PARENT_NODE, $classConst);
        $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($property);
        $phpDocInfo->markAsChanged();
        $classConst->setAttribute(AttributeKey::PHP_DOC_INFO, $phpDocInfo);
        $classConst->setAttribute(AttributeKey::PARENT_NODE, $property->getAttribute(AttributeKey::PARENT_NODE));
        return $classConst;
    }
}
