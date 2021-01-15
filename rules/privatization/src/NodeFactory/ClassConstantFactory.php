<?php

declare(strict_types=1);

namespace Rector\Privatization\NodeFactory;

use PhpParser\Node\Const_;
use PhpParser\Node\Expr;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassConst;
use PhpParser\Node\Stmt\Property;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\Privatization\Naming\ConstantNaming;

final class ClassConstantFactory
{
    /**
     * @var NodeNameResolver
     */
    private $nodeNameResolver;

    /**
     * @var ConstantNaming
     */
    private $constantNaming;

    public function __construct(NodeNameResolver $nodeNameResolver, ConstantNaming $constantNaming)
    {
        $this->nodeNameResolver = $nodeNameResolver;
        $this->constantNaming = $constantNaming;
    }

    public function createFromProperty(Property $property): ClassConst
    {
        $propertyProperty = $property->props[0];

        $constantName = $this->constantNaming->createFromProperty($propertyProperty);

        /** @var Expr $defaultValue */
        $defaultValue = $propertyProperty->default;
        $const = new Const_($constantName, $defaultValue);

        $classConst = new ClassConst([$const]);
        $classConst->flags = $property->flags & ~ Class_::MODIFIER_STATIC;

        $classConst->setAttribute(AttributeKey::PHP_DOC_INFO, $property->getAttribute(AttributeKey::PHP_DOC_INFO));

        return $classConst;
    }
}
