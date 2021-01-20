<?php

declare(strict_types=1);

namespace Rector\Php80\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Param;
use Rector\Core\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @see https://php.watch/versions/8.0#deprecate-required-param-after-optional
 *
 * @see \Rector\Php80\Tests\Rector\ClassMethod\OptionalParametersAfterRequiredRector\OptionalParametersAfterRequiredRectorTest
 */
final class OptionalParametersAfterRequiredRector extends AbstractRector
{
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Move required parameters after optional ones', [
            new CodeSample(
                <<<'CODE_SAMPLE'
class SomeObject
{
    public function run($optional = 1, $required)
    {
    }
}
CODE_SAMPLE

                ,
                <<<'CODE_SAMPLE'
class SomeObject
{
    public function run($required, $optional = 1)
    {
    }
}
CODE_SAMPLE

            ),
        ]);
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [\PhpParser\Node\Stmt\ClassMethod::class];
    }

    /**
     * @param \PhpParser\Node\Stmt\ClassMethod $node
     */
    public function refactor(Node $node): ?Node
    {
        if ($node->params === []) {
            return null;
        }

        $requireParams = $this->resolveRequiredParams($node);
        $optionalParams = $this->resolveOptionalParams($node);

        $expectedOrderParams = array_merge($requireParams, $optionalParams);
        if ($node->params === $expectedOrderParams) {
            return null;
        }

        $node->params = $expectedOrderParams;

        return $node;
    }

    /**
     * @return array<int, Param>
     */
    private function resolveOptionalParams(Node\Stmt\ClassMethod $classMethod): array
    {
        $paramsByPosition = [];
        foreach ($classMethod->params as $position => $param) {
            if ($param->default === null) {
                continue;
            }

            $paramsByPosition[$position] = $param;
        }

        return $paramsByPosition;
    }

    /**
     * @return Param[]
     */
    private function resolveRequiredParams(Node\Stmt\ClassMethod $classMethod): array
    {
        $paramsByPosition = [];
        foreach ($classMethod->params as $position => $param) {
            if ($param->default !== null) {
                continue;
            }

            $paramsByPosition[] = $param;
        }

        return $paramsByPosition;
    }
}
