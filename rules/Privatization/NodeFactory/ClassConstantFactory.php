<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Rector\Privatization\NodeFactory;

use RectorPrefix20220606\PhpParser\Node\Const_;
use RectorPrefix20220606\PhpParser\Node\Expr;
use RectorPrefix20220606\PhpParser\Node\Stmt\Class_;
use RectorPrefix20220606\PhpParser\Node\Stmt\ClassConst;
use RectorPrefix20220606\PhpParser\Node\Stmt\Property;
use RectorPrefix20220606\Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory;
use RectorPrefix20220606\Rector\NodeTypeResolver\Node\AttributeKey;
use RectorPrefix20220606\Rector\Privatization\Naming\ConstantNaming;
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
        $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($property);
        $phpDocInfo->markAsChanged();
        $classConst->setAttribute(AttributeKey::PHP_DOC_INFO, $phpDocInfo);
        return $classConst;
    }
}
