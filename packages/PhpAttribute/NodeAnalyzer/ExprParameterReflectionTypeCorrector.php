<?php

declare (strict_types=1);
namespace Rector\PhpAttribute\NodeAnalyzer;

use PhpParser\Node\Expr;
use PhpParser\Node\Scalar\LNumber;
use PhpParser\Node\Scalar\String_;
use PHPStan\Reflection\ParameterReflection;
use PHPStan\Reflection\ParametersAcceptorSelector;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Type\BooleanType;
use PHPStan\Type\IntegerType;
use PHPStan\Type\TypeCombinator;
use Rector\Core\PhpParser\Node\NodeFactory;
use Rector\StaticTypeMapper\StaticTypeMapper;
final class ExprParameterReflectionTypeCorrector
{
    /**
     * @readonly
     * @var \Rector\StaticTypeMapper\StaticTypeMapper
     */
    private $staticTypeMapper;
    /**
     * @readonly
     * @var \PHPStan\Reflection\ReflectionProvider
     */
    private $reflectionProvider;
    /**
     * @readonly
     * @var \Rector\Core\PhpParser\Node\NodeFactory
     */
    private $nodeFactory;
    public function __construct(\Rector\StaticTypeMapper\StaticTypeMapper $staticTypeMapper, \PHPStan\Reflection\ReflectionProvider $reflectionProvider, \Rector\Core\PhpParser\Node\NodeFactory $nodeFactory)
    {
        $this->staticTypeMapper = $staticTypeMapper;
        $this->reflectionProvider = $reflectionProvider;
        $this->nodeFactory = $nodeFactory;
    }
    /**
     * @param array<string|int, Expr|mixed> $items
     * @return array<string|int, Expr|mixed>
     */
    public function correctItemsByAttributeClass(array $items, string $attributeClass) : array
    {
        if (!$this->reflectionProvider->hasClass($attributeClass)) {
            return $items;
        }
        $attributeClassReflection = $this->reflectionProvider->getClass($attributeClass);
        // nothing to retype by constructor
        if (!$attributeClassReflection->hasConstructor()) {
            return $items;
        }
        $constructorClassMethodReflection = $attributeClassReflection->getConstructor();
        $parametersAcceptor = \PHPStan\Reflection\ParametersAcceptorSelector::selectSingle($constructorClassMethodReflection->getVariants());
        foreach ($items as $name => $item) {
            foreach ($parametersAcceptor->getParameters() as $parameterReflection) {
                $correctedItem = $this->correctItemByParameterReflection($name, $item, $parameterReflection);
                if (!$correctedItem instanceof \PhpParser\Node\Expr) {
                    continue;
                }
                $items[$name] = $correctedItem;
                continue 2;
            }
        }
        return $items;
    }
    /**
     * @param int|string $name
     * @return \PhpParser\Node\Expr|null
     * @param mixed $item
     */
    private function correctItemByParameterReflection($name, $item, \PHPStan\Reflection\ParameterReflection $parameterReflection)
    {
        if (!$item instanceof \PhpParser\Node\Expr) {
            return null;
        }
        if ($name !== $parameterReflection->getName()) {
            return null;
        }
        $parameterType = $parameterReflection->getType();
        $currentType = $this->staticTypeMapper->mapPhpParserNodePHPStanType($item);
        // all good
        if ($parameterType->accepts($currentType, \false)->yes()) {
            return null;
        }
        $clearParameterType = \PHPStan\Type\TypeCombinator::removeNull($parameterType);
        // correct type
        if ($clearParameterType instanceof \PHPStan\Type\IntegerType && $item instanceof \PhpParser\Node\Scalar\String_) {
            return new \PhpParser\Node\Scalar\LNumber((int) $item->value);
        }
        if ($clearParameterType instanceof \PHPStan\Type\BooleanType && $item instanceof \PhpParser\Node\Scalar\String_) {
            if (\strtolower($item->value) === 'true') {
                return $this->nodeFactory->createTrue();
            }
            if (\strtolower($item->value) === 'false') {
                return $this->nodeFactory->createFalse();
            }
        }
        return null;
    }
}
