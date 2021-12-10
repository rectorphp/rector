<?php

declare (strict_types=1);
namespace Rector\PHPOffice\Rector\StaticCall;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use PHPStan\Type\ObjectType;
use Rector\Core\Rector\AbstractRector;
use Rector\PHPOffice\ValueObject\PHPExcelMethodDefaultValues;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://github.com/PHPOffice/PhpSpreadsheet/commit/033a4bdad56340795a5bf7ec3c8a2fde005cda24 https://github.com/PHPOffice/PhpSpreadsheet/blob/master/docs/topics/migration-from-PHPExcel.md#removed-default-values
 *
 * @see \Rector\PHPOffice\Tests\Rector\StaticCall\AddRemovedDefaultValuesRector\AddRemovedDefaultValuesRectorTest
 */
final class AddRemovedDefaultValuesRector extends \Rector\Core\Rector\AbstractRector
{
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Complete removed default values explicitly', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
final class SomeClass
{
    public function run(): void
    {
        $logger = new \PHPExcel_CalcEngine_Logger;
        $logger->setWriteDebugLog();
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
final class SomeClass
{
    public function run(): void
    {
        $logger = new \PHPExcel_CalcEngine_Logger;
        $logger->setWriteDebugLog(false);
    }
}
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [\PhpParser\Node\Expr\StaticCall::class, \PhpParser\Node\Expr\MethodCall::class];
    }
    /**
     * @param StaticCall|MethodCall $node
     * @return \PhpParser\Node\Expr\MethodCall|\PhpParser\Node\Expr\StaticCall
     */
    public function refactor(\PhpParser\Node $node)
    {
        foreach (\Rector\PHPOffice\ValueObject\PHPExcelMethodDefaultValues::METHOD_NAMES_BY_TYPE_WITH_VALUE as $type => $defaultValuesByMethodName) {
            if (!$this->isCallerObjectType($node, new \PHPStan\Type\ObjectType($type))) {
                continue;
            }
            foreach ($defaultValuesByMethodName as $methodName => $defaultValuesByPosition) {
                if (!$this->isName($node->name, $methodName)) {
                    continue;
                }
                $this->refactorArgs($node, $defaultValuesByPosition);
            }
        }
        return $node;
    }
    /**
     * @param array<int, mixed> $defaultValuesByPosition
     * @param \PhpParser\Node\Expr\MethodCall|\PhpParser\Node\Expr\StaticCall $node
     */
    private function refactorArgs($node, array $defaultValuesByPosition) : void
    {
        foreach ($defaultValuesByPosition as $position => $defaultValue) {
            // value is already set
            if (isset($node->args[$position])) {
                continue;
            }
            if (\is_string($defaultValue) && \strpos($defaultValue, '::') !== \false) {
                [$className, $constant] = \explode('::', $defaultValue);
                $classConstant = $this->nodeFactory->createClassConstFetch($className, $constant);
                $arg = new \PhpParser\Node\Arg($classConstant);
            } else {
                $arg = $this->nodeFactory->createArg($defaultValue);
            }
            $node->args[$position] = $arg;
        }
    }
    /**
     * @param \PhpParser\Node\Expr\MethodCall|\PhpParser\Node\Expr\StaticCall $node
     */
    private function isCallerObjectType($node, \PHPStan\Type\ObjectType $objectType) : bool
    {
        $caller = $node instanceof \PhpParser\Node\Expr\MethodCall ? $node->var : $node->class;
        return $this->isObjectType($caller, $objectType);
    }
}
