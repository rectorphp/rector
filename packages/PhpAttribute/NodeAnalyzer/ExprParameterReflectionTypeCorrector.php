<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Rector\PhpAttribute\NodeAnalyzer;

use RectorPrefix20220606\PhpParser\Node\Expr;
use RectorPrefix20220606\PhpParser\Node\Expr\Array_;
use RectorPrefix20220606\PhpParser\Node\Scalar\LNumber;
use RectorPrefix20220606\PhpParser\Node\Scalar\String_;
use RectorPrefix20220606\PHPStan\Reflection\ParameterReflection;
use RectorPrefix20220606\PHPStan\Reflection\ParametersAcceptorSelector;
use RectorPrefix20220606\PHPStan\Reflection\ReflectionProvider;
use RectorPrefix20220606\PHPStan\Type\BooleanType;
use RectorPrefix20220606\PHPStan\Type\IntegerType;
use RectorPrefix20220606\PHPStan\Type\TypeCombinator;
use RectorPrefix20220606\Rector\Core\PhpParser\Node\NodeFactory;
use RectorPrefix20220606\Rector\StaticTypeMapper\StaticTypeMapper;
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
    public function __construct(StaticTypeMapper $staticTypeMapper, ReflectionProvider $reflectionProvider, NodeFactory $nodeFactory)
    {
        $this->staticTypeMapper = $staticTypeMapper;
        $this->reflectionProvider = $reflectionProvider;
        $this->nodeFactory = $nodeFactory;
    }
    /**
     * @param array<string|int, Expr|mixed> $items
     * @return array<string|int, Expr|mixed>
     */
    public function correctItemsByAttributeClass($items, string $attributeClass) : array
    {
        if ($items instanceof Array_) {
            $items = $items->items;
        }
        if (!$this->reflectionProvider->hasClass($attributeClass)) {
            return $items;
        }
        $attributeClassReflection = $this->reflectionProvider->getClass($attributeClass);
        // nothing to retype by constructor
        if (!$attributeClassReflection->hasConstructor()) {
            return $items;
        }
        $constructorClassMethodReflection = $attributeClassReflection->getConstructor();
        $parametersAcceptor = ParametersAcceptorSelector::selectSingle($constructorClassMethodReflection->getVariants());
        foreach ($items as $name => $item) {
            foreach ($parametersAcceptor->getParameters() as $parameterReflection) {
                $correctedItem = $this->correctItemByParameterReflection($name, $item, $parameterReflection);
                if (!$correctedItem instanceof Expr) {
                    continue;
                }
                $items[$name] = $correctedItem;
                continue 2;
            }
        }
        return $items;
    }
    /**
     * @param string|int $name
     * @return \PhpParser\Node\Expr|null
     * @param mixed $item
     */
    private function correctItemByParameterReflection($name, $item, ParameterReflection $parameterReflection)
    {
        if (!$item instanceof Expr) {
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
        $clearParameterType = TypeCombinator::removeNull($parameterType);
        // correct type
        if ($clearParameterType instanceof IntegerType && $item instanceof String_) {
            return new LNumber((int) $item->value);
        }
        if ($clearParameterType instanceof BooleanType && $item instanceof String_) {
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
