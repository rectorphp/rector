<?php

declare (strict_types=1);
namespace Rector\PHPUnit\NodeManipulator;

use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\ArrayItem;
use PhpParser\Node\Expr\Variable;
use PHPStan\Type\Type;
use Rector\NodeTypeResolver\NodeTypeResolver;
use Rector\NodeTypeResolver\PHPStan\Type\TypeFactory;
use Rector\PHPUnit\ValueObject\ParamAndArg;
final class ParamAndArgFromArrayResolver
{
    /**
     * @readonly
     * @var \Rector\NodeTypeResolver\NodeTypeResolver
     */
    private $nodeTypeResolver;
    /**
     * @readonly
     * @var \Rector\NodeTypeResolver\PHPStan\Type\TypeFactory
     */
    private $typeFactory;
    public function __construct(\Rector\NodeTypeResolver\NodeTypeResolver $nodeTypeResolver, \Rector\NodeTypeResolver\PHPStan\Type\TypeFactory $typeFactory)
    {
        $this->nodeTypeResolver = $nodeTypeResolver;
        $this->typeFactory = $typeFactory;
    }
    /**
     * @return ParamAndArg[]
     */
    public function resolve(\PhpParser\Node\Expr\Array_ $array, string $variableName) : array
    {
        $isNestedArray = $this->isNestedArray($array);
        if ($isNestedArray) {
            return $this->collectParamAndArgsFromNestedArray($array, $variableName);
        }
        $itemsStaticType = $this->resolveItemStaticType($array, $isNestedArray);
        return $this->collectParamAndArgsFromNonNestedArray($array, $variableName, $itemsStaticType);
    }
    private function isNestedArray(\PhpParser\Node\Expr\Array_ $array) : bool
    {
        foreach ($array->items as $arrayItem) {
            if (!$arrayItem instanceof \PhpParser\Node\Expr\ArrayItem) {
                continue;
            }
            if ($arrayItem->value instanceof \PhpParser\Node\Expr\Array_) {
                return \true;
            }
        }
        return \false;
    }
    /**
     * @return ParamAndArg[]
     */
    private function collectParamAndArgsFromNestedArray(\PhpParser\Node\Expr\Array_ $array, string $variableName) : array
    {
        $paramAndArgs = [];
        $i = 1;
        foreach ($array->items as $arrayItem) {
            if (!$arrayItem instanceof \PhpParser\Node\Expr\ArrayItem) {
                continue;
            }
            $nestedArray = $arrayItem->value;
            if (!$nestedArray instanceof \PhpParser\Node\Expr\Array_) {
                continue;
            }
            foreach ($nestedArray->items as $nestedArrayItem) {
                if (!$nestedArrayItem instanceof \PhpParser\Node\Expr\ArrayItem) {
                    continue;
                }
                $variable = new \PhpParser\Node\Expr\Variable($variableName . ($i === 1 ? '' : $i));
                $itemsStaticType = $this->nodeTypeResolver->getType($nestedArrayItem->value);
                $paramAndArgs[] = new \Rector\PHPUnit\ValueObject\ParamAndArg($variable, $itemsStaticType);
                ++$i;
            }
        }
        return $paramAndArgs;
    }
    private function resolveItemStaticType(\PhpParser\Node\Expr\Array_ $array, bool $isNestedArray) : \PHPStan\Type\Type
    {
        $staticTypes = [];
        if (!$isNestedArray) {
            foreach ($array->items as $arrayItem) {
                if (!$arrayItem instanceof \PhpParser\Node\Expr\ArrayItem) {
                    continue;
                }
                $staticTypes[] = $this->nodeTypeResolver->getType($arrayItem->value);
            }
        }
        return $this->typeFactory->createMixedPassedOrUnionType($staticTypes);
    }
    /**
     * @return ParamAndArg[]
     */
    private function collectParamAndArgsFromNonNestedArray(\PhpParser\Node\Expr\Array_ $array, string $variableName, \PHPStan\Type\Type $itemsStaticType) : array
    {
        $i = 1;
        $paramAndArgs = [];
        foreach ($array->items as $arrayItem) {
            if (!$arrayItem instanceof \PhpParser\Node\Expr\ArrayItem) {
                continue;
            }
            $variable = new \PhpParser\Node\Expr\Variable($variableName . ($i === 1 ? '' : $i));
            $paramAndArgs[] = new \Rector\PHPUnit\ValueObject\ParamAndArg($variable, $itemsStaticType);
            ++$i;
            if (!$arrayItem->value instanceof \PhpParser\Node\Expr\Array_) {
                break;
            }
        }
        return $paramAndArgs;
    }
}
