<?php

declare (strict_types=1);
namespace PHPStan\Type\PHPUnit\Assert;

use PhpParser\Node\Arg;
use PhpParser\Node\Expr\BinaryOp\Identical;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\Instanceof_;
use PhpParser\Node\Name;
use PHPStan\Analyser\Scope;
use PHPStan\Analyser\SpecifiedTypes;
use PHPStan\Analyser\TypeSpecifier;
use PHPStan\Analyser\TypeSpecifierContext;
use PHPStan\Type\Constant\ConstantStringType;
class AssertTypeSpecifyingExtensionHelper
{
    /** @var \Closure[] */
    private static $resolvers;
    /**
     * @param string $name
     * @param \PhpParser\Node\Arg[] $args
     * @return bool
     */
    public static function isSupported(string $name, array $args) : bool
    {
        $trimmedName = self::trimName($name);
        $resolvers = self::getExpressionResolvers();
        if (!\array_key_exists($trimmedName, $resolvers)) {
            return \false;
        }
        $resolver = $resolvers[$trimmedName];
        $resolverReflection = new \ReflectionObject($resolver);
        return \count($args) >= \count($resolverReflection->getMethod('__invoke')->getParameters()) - 1;
    }
    private static function trimName(string $name) : string
    {
        if (\strpos($name, 'assert') !== 0) {
            return $name;
        }
        $name = \substr($name, \strlen('assert'));
        if (\strpos($name, 'Not') === 0) {
            return \substr($name, 3);
        }
        if (\strpos($name, 'IsNot') === 0) {
            return 'Is' . \substr($name, 5);
        }
        return $name;
    }
    /**
     * @param TypeSpecifier $typeSpecifier
     * @param Scope $scope
     * @param string $name
     * @param \PhpParser\Node\Arg[] $args $args
     * @return SpecifiedTypes
     */
    public static function specifyTypes(TypeSpecifier $typeSpecifier, Scope $scope, string $name, array $args) : SpecifiedTypes
    {
        $expression = self::createExpression($scope, $name, $args);
        if ($expression === null) {
            return new SpecifiedTypes([], []);
        }
        return $typeSpecifier->specifyTypesInCondition($scope, $expression, TypeSpecifierContext::createTruthy());
    }
    /**
     * @param Scope $scope
     * @param string $name
     * @param \PhpParser\Node\Arg[] $args
     * @return \PhpParser\Node\Expr|null
     */
    private static function createExpression(Scope $scope, string $name, array $args) : ?\PhpParser\Node\Expr
    {
        $trimmedName = self::trimName($name);
        $resolvers = self::getExpressionResolvers();
        $resolver = $resolvers[$trimmedName];
        $expression = $resolver($scope, ...$args);
        if ($expression === null) {
            return null;
        }
        if (\strpos($name, 'Not') !== \false) {
            $expression = new \PhpParser\Node\Expr\BooleanNot($expression);
        }
        return $expression;
    }
    /**
     * @return \Closure[]
     */
    private static function getExpressionResolvers() : array
    {
        if (self::$resolvers === null) {
            self::$resolvers = ['InstanceOf' => function (Scope $scope, Arg $class, Arg $object) : ?Instanceof_ {
                $classType = $scope->getType($class->value);
                if (!$classType instanceof ConstantStringType) {
                    return null;
                }
                return new \PhpParser\Node\Expr\Instanceof_($object->value, new \PhpParser\Node\Name($classType->getValue()));
            }, 'Same' => function (Scope $scope, Arg $expected, Arg $actual) : Identical {
                return new \PhpParser\Node\Expr\BinaryOp\Identical($expected->value, $actual->value);
            }, 'True' => function (Scope $scope, Arg $actual) : Identical {
                return new \PhpParser\Node\Expr\BinaryOp\Identical($actual->value, new \PhpParser\Node\Expr\ConstFetch(new Name('true')));
            }, 'False' => function (Scope $scope, Arg $actual) : Identical {
                return new \PhpParser\Node\Expr\BinaryOp\Identical($actual->value, new \PhpParser\Node\Expr\ConstFetch(new Name('false')));
            }, 'Null' => function (Scope $scope, Arg $actual) : Identical {
                return new \PhpParser\Node\Expr\BinaryOp\Identical($actual->value, new \PhpParser\Node\Expr\ConstFetch(new Name('null')));
            }, 'IsArray' => function (Scope $scope, Arg $actual) : FuncCall {
                return new \PhpParser\Node\Expr\FuncCall(new Name('is_array'), [$actual]);
            }, 'IsBool' => function (Scope $scope, Arg $actual) : FuncCall {
                return new \PhpParser\Node\Expr\FuncCall(new Name('is_bool'), [$actual]);
            }, 'IsCallable' => function (Scope $scope, Arg $actual) : FuncCall {
                return new \PhpParser\Node\Expr\FuncCall(new Name('is_callable'), [$actual]);
            }, 'IsFloat' => function (Scope $scope, Arg $actual) : FuncCall {
                return new \PhpParser\Node\Expr\FuncCall(new Name('is_float'), [$actual]);
            }, 'IsInt' => function (Scope $scope, Arg $actual) : FuncCall {
                return new \PhpParser\Node\Expr\FuncCall(new Name('is_int'), [$actual]);
            }, 'IsIterable' => function (Scope $scope, Arg $actual) : FuncCall {
                return new \PhpParser\Node\Expr\FuncCall(new Name('is_iterable'), [$actual]);
            }, 'IsNumeric' => function (Scope $scope, Arg $actual) : FuncCall {
                return new \PhpParser\Node\Expr\FuncCall(new Name('is_numeric'), [$actual]);
            }, 'IsObject' => function (Scope $scope, Arg $actual) : FuncCall {
                return new \PhpParser\Node\Expr\FuncCall(new Name('is_object'), [$actual]);
            }, 'IsResource' => function (Scope $scope, Arg $actual) : FuncCall {
                return new \PhpParser\Node\Expr\FuncCall(new Name('is_resource'), [$actual]);
            }, 'IsString' => function (Scope $scope, Arg $actual) : FuncCall {
                return new \PhpParser\Node\Expr\FuncCall(new Name('is_string'), [$actual]);
            }, 'IsScalar' => function (Scope $scope, Arg $actual) : FuncCall {
                return new \PhpParser\Node\Expr\FuncCall(new Name('is_scalar'), [$actual]);
            }, 'InternalType' => function (Scope $scope, Arg $type, Arg $value) : ?FuncCall {
                $typeType = $scope->getType($type->value);
                if (!$typeType instanceof ConstantStringType) {
                    return null;
                }
                switch ($typeType->getValue()) {
                    case 'numeric':
                        $functionName = 'is_numeric';
                        break;
                    case 'integer':
                    case 'int':
                        $functionName = 'is_int';
                        break;
                    case 'double':
                    case 'float':
                    case 'real':
                        $functionName = 'is_float';
                        break;
                    case 'string':
                        $functionName = 'is_string';
                        break;
                    case 'boolean':
                    case 'bool':
                        $functionName = 'is_bool';
                        break;
                    case 'scalar':
                        $functionName = 'is_scalar';
                        break;
                    case 'null':
                        $functionName = 'is_null';
                        break;
                    case 'array':
                        $functionName = 'is_array';
                        break;
                    case 'object':
                        $functionName = 'is_object';
                        break;
                    case 'resource':
                        $functionName = 'is_resource';
                        break;
                    case 'callable':
                        $functionName = 'is_callable';
                        break;
                    default:
                        return null;
                }
                return new \PhpParser\Node\Expr\FuncCall(new Name($functionName), [$value]);
            }];
        }
        return self::$resolvers;
    }
}
