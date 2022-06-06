<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Rector\PHPOffice\Rector\StaticCall;

use RectorPrefix20220606\PhpParser\Node;
use RectorPrefix20220606\PhpParser\Node\Arg;
use RectorPrefix20220606\PhpParser\Node\Expr\MethodCall;
use RectorPrefix20220606\PhpParser\Node\Expr\StaticCall;
use RectorPrefix20220606\PHPStan\Type\ObjectType;
use RectorPrefix20220606\Rector\Core\Rector\AbstractRector;
use RectorPrefix20220606\Rector\PHPOffice\ValueObject\PHPExcelMethodDefaultValues;
use RectorPrefix20220606\Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use RectorPrefix20220606\Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://github.com/PHPOffice/PhpSpreadsheet/commit/033a4bdad56340795a5bf7ec3c8a2fde005cda24 https://github.com/PHPOffice/PhpSpreadsheet/blob/master/docs/topics/migration-from-PHPExcel.md#removed-default-values
 *
 * @see \Rector\PHPOffice\Tests\Rector\StaticCall\AddRemovedDefaultValuesRector\AddRemovedDefaultValuesRectorTest
 */
final class AddRemovedDefaultValuesRector extends AbstractRector
{
    /**
     * @var bool
     */
    private $hasChanged = \false;
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Complete removed default values explicitly', [new CodeSample(<<<'CODE_SAMPLE'
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
        return [StaticCall::class, MethodCall::class];
    }
    /**
     * @param StaticCall|MethodCall $node
     * @return null|\PhpParser\Node\Expr\StaticCall|\PhpParser\Node\Expr\MethodCall
     */
    public function refactor(Node $node)
    {
        foreach (PHPExcelMethodDefaultValues::METHOD_NAMES_BY_TYPE_WITH_VALUE as $type => $defaultValuesByMethodName) {
            if (!$this->isCallerObjectType($node, new ObjectType($type))) {
                continue;
            }
            foreach ($defaultValuesByMethodName as $methodName => $defaultValuesByPosition) {
                if (!$this->isName($node->name, $methodName)) {
                    continue;
                }
                $this->refactorArgs($node, $defaultValuesByPosition);
            }
        }
        if (!$this->hasChanged) {
            return null;
        }
        return $node;
    }
    /**
     * @param array<int, mixed> $defaultValuesByPosition
     * @param \PhpParser\Node\Expr\StaticCall|\PhpParser\Node\Expr\MethodCall $node
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
                $arg = new Arg($classConstant);
            } else {
                $arg = $this->nodeFactory->createArg($defaultValue);
            }
            $node->args[$position] = $arg;
            $this->hasChanged = \true;
        }
    }
    /**
     * @param \PhpParser\Node\Expr\StaticCall|\PhpParser\Node\Expr\MethodCall $node
     */
    private function isCallerObjectType($node, ObjectType $objectType) : bool
    {
        $caller = $node instanceof MethodCall ? $node->var : $node->class;
        return $this->isObjectType($caller, $objectType);
    }
}
