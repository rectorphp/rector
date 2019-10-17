<?php

declare(strict_types=1);

namespace Rector\CodeQuality\Rector\FuncCall;

use PhpParser\Node;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\FuncCall;
use PHPStan\Type\ArrayType;
use PHPStan\Type\Constant\ConstantArrayType;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;

/**
 * @see https://3v4l.org/aLf96
 *
 * @see \Rector\CodeQuality\Tests\Rector\FuncCall\ArrayMergeOfNonArraysToSimpleArrayRector\ArrayMergeOfNonArraysToSimpleArrayRectorTest
 */
final class ArrayMergeOfNonArraysToSimpleArrayRector extends AbstractRector
{
    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Change array_merge of non arrays to array directly', [
            new CodeSample(
                <<<'PHP'
class SomeClass
{
    public function go()
    {
        $value = 5;
        $value2 = 10;

        return array_merge([$value, $value2]);
    }
}
PHP
,
                <<<'PHP'
class SomeClass
{
    public function go()
    {
        $value = 5;
        $value2 = 10;

        return [$value, $value2];
    }
}
PHP

            ),
        ]);
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [FuncCall::class];
    }

    /**
     * @param FuncCall $node
     */
    public function refactor(Node $node): ?Node
    {
        if (! $this->isName($node, 'array_merge')) {
            return null;
        }

        if ($this->shouldSkip($node)) {
            return null;
        }

        $array = new Array_();
        foreach ($node->args as $arg) {
            /** @var Array_ $nestedArrayItem */
            $nestedArrayItem = $arg->value;

            // skip
            if (! $nestedArrayItem instanceof Array_) {
                return null;
            }

            foreach ($nestedArrayItem->items as $nestedArrayItemItem) {
                $array->items[] = $nestedArrayItemItem->value;
            }
        }

        return $array;
    }

    private function shouldSkip(Node $node): bool
    {
        foreach ($node->args as $arg) {
            $argumentValueStaticType = $this->getStaticType($arg->value);

            if (! $argumentValueStaticType instanceof ConstantArrayType) {
                return true;
            }

            foreach ($argumentValueStaticType->getValueTypes() as $valueType) {
                // nested array → skip
                if ($valueType instanceof ArrayType) {
                    return true;
                }
            }
        }

        return false;
    }
}
