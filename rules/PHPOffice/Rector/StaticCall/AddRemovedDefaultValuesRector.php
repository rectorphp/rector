<?php

declare(strict_types=1);

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
 * @see \Rector\Tests\PHPOffice\Rector\StaticCall\AddRemovedDefaultValuesRector\AddRemovedDefaultValuesRectorTest
 */
final class AddRemovedDefaultValuesRector extends AbstractRector
{
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Complete removed default values explicitly', [
            new CodeSample(
                <<<'CODE_SAMPLE'
final class SomeClass
{
    public function run(): void
    {
        $logger = new \PHPExcel_CalcEngine_Logger;
        $logger->setWriteDebugLog();
    }
}
CODE_SAMPLE
,
                <<<'CODE_SAMPLE'
final class SomeClass
{
    public function run(): void
    {
        $logger = new \PHPExcel_CalcEngine_Logger;
        $logger->setWriteDebugLog(false);
    }
}
CODE_SAMPLE
            ),
        ]);
    }

    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [StaticCall::class, MethodCall::class];
    }

    /**
     * @param StaticCall|MethodCall $node
     */
    public function refactor(Node $node): ?Node
    {
        foreach (PHPExcelMethodDefaultValues::METHOD_NAMES_BY_TYPE_WITH_VALUE as $type => $defaultValuesByMethodName) {
            if (! $this->isCallerObjectType($node, new ObjectType($type))) {
                continue;
            }

            foreach ($defaultValuesByMethodName as $methodName => $defaultValuesByPosition) {
                if (! $this->isName($node->name, $methodName)) {
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
    private function refactorArgs(Node $node, array $defaultValuesByPosition): void
    {
        foreach ($defaultValuesByPosition as $position => $defaultValue) {
            // value is already set
            if (isset($node->args[$position])) {
                continue;
            }

            if (is_string($defaultValue) && \str_contains($defaultValue, '::')) {
                [$className, $constant] = explode('::', $defaultValue);
                $classConstant = $this->nodeFactory->createClassConstFetch($className, $constant);
                $arg = new Arg($classConstant);
            } else {
                $arg = $this->nodeFactory->createArg($defaultValue);
            }

            $node->args[$position] = $arg;
        }
    }

    /**
     * @param StaticCall|MethodCall $node
     */
    private function isCallerObjectType(Node $node, ObjectType $objectType): bool
    {
        return $this->isObjectType($node instanceof MethodCall ? $node->var : $node->class, $objectType);
    }
}
