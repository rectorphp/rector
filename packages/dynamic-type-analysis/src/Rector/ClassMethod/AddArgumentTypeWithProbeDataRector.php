<?php

declare(strict_types=1);

namespace Rector\DynamicTypeAnalysis\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Stmt\ClassMethod;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;
use Rector\DynamicTypeAnalysis\Probe\TypeStaticProbe;

/**
 * @see \Rector\DynamicTypeAnalysis\Tests\Rector\ClassMethod\AddArgumentTypeWithProbeDataRector\AddArgumentTypeWithProbeDataRectorTest
 */
final class AddArgumentTypeWithProbeDataRector extends AbstractArgumentProbeRector
{
    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Add argument type based on probed data', [
            new CodeSample(
                <<<'PHP'
class SomeClass
{
    public function run($arg)
    {
    }
}
PHP
,
                <<<'PHP'
class SomeClass
{
    public function run(string $arg)
    {
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
        return [ClassMethod::class];
    }

    /**
     * @param ClassMethod $node
     */
    public function refactor(Node $node): ?Node
    {
        if ($this->shouldSkipClassMethod($node)) {
            return null;
        }

        $classMethodReference = $this->getClassMethodReference($node);
        if ($classMethodReference === null) {
            return null;
        }

        $methodData = TypeStaticProbe::getDataForMethodByPosition($classMethodReference);
        // no data for this method → skip
        if ($methodData === []) {
            return null;
        }

        $this->completeTypesToClassMethodParams($node, $methodData);

        return $node;
    }

    /**
     * @param mixed[] $methodData
     */
    private function completeTypesToClassMethodParams(ClassMethod $classMethod, array $methodData): void
    {
        foreach ($classMethod->params as $position => $param) {
            // do not override existing types
            if ($param->type !== null) {
                continue;
            }

            // no data for this parameter → skip
            if (! isset($methodData[$position])) {
                continue;
            }

            $parameterData = $methodData[$position];
            // uniquate data
            $parameterData = array_unique($parameterData);

            if (count($parameterData) > 1) {
                // is union or nullable type?
                continue;
            }

            // single value → can we add it?
            if (count($parameterData) === 1) {
                $typeNode = $this->staticTypeMapper->mapStringToPhpParserNode($parameterData[0]);
                $param->type = $typeNode;
            }
        }
    }
}
