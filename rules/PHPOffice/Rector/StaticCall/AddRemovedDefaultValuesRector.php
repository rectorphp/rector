<?php

declare (strict_types=1);
namespace Rector\PHPOffice\Rector\StaticCall;

use RectorPrefix20210514\Nette\Utils\Strings;
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
 * @see \Rector\Tests\PHPOffice\Rector\StaticCall\AddRemovedDefaultValuesRector\AddRemovedDefaultValuesRectorTest
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
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
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
     * @param StaticCall|MethodCall $node
     * @param array<int, mixed> $defaultValuesByPosition
     */
    private function refactorArgs(\PhpParser\Node $node, array $defaultValuesByPosition) : void
    {
        foreach ($defaultValuesByPosition as $position => $defaultValue) {
            // value is already set
            if (isset($node->args[$position])) {
                continue;
            }
            if (\is_string($defaultValue) && \RectorPrefix20210514\Nette\Utils\Strings::contains($defaultValue, '::')) {
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
     * @param StaticCall|MethodCall $node
     */
    private function isCallerObjectType(\PhpParser\Node $node, \PHPStan\Type\ObjectType $objectType) : bool
    {
        return $this->isObjectType($node instanceof \PhpParser\Node\Expr\MethodCall ? $node->var : $node->class, $objectType);
    }
}
