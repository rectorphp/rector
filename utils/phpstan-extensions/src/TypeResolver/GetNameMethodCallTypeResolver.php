<?php

declare(strict_types=1);

namespace Rector\PHPStanExtensions\TypeResolver;

use PhpParser\Node;
use PhpParser\Node\Const_ as NodeConst;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Name;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Const_;
use PhpParser\Node\Stmt\Interface_;
use PhpParser\Node\Stmt\Property;
use PhpParser\Node\Stmt\PropertyProperty;
use PhpParser\Node\Stmt\Trait_;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Reflection\ParametersAcceptorSelector;
use PHPStan\Type\ObjectType;
use PHPStan\Type\StringType;
use PHPStan\Type\Type;

final class GetNameMethodCallTypeResolver
{
    /**
     * @var class-string<Node>
     */
    private const ALWAYS_NAMED_TYPES = [
        ClassMethod::class,
        Trait_::class,
        Interface_::class,
        Property::class,
        PropertyProperty::class,
        Const_::class,
        NodeConst::class,
        Param::class,
        Name::class,
    ];

    public function resolveFromMethodCall(
        MethodReflection $methodReflection,
        MethodCall $methodCall,
        Scope $scope
    ): Type {
        $returnType = ParametersAcceptorSelector::selectSingle($methodReflection->getVariants())->getReturnType();

        $argumentValueType = $scope->getType($methodCall->args[0]->value);
        if (! $argumentValueType instanceof ObjectType) {
            return $returnType;
        }

        if (in_array($argumentValueType->getClassName(), self::ALWAYS_NAMED_TYPES, true)) {
            return new StringType();
        }

        return $returnType;
    }
}
