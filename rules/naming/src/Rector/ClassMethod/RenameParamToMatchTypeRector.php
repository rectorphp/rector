<?php

declare(strict_types=1);

namespace Rector\Naming\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\ClassMethod;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;
use Rector\Naming\ExpectedNameResolver\MatchParamTypeExpectedNameResolver;
use Rector\Naming\Guard\BreakingVariableRenameGuard;
use Rector\Naming\Naming\ExpectedNameResolver;
use Rector\Naming\ParamRenamer\MatchTypeParamRenamer;
use Rector\Naming\ValueObjectFactory\ParamRenameFactory;

/**
 * @see \Rector\Naming\Tests\Rector\ClassMethod\RenameParamToMatchTypeRector\RenameParamToMatchTypeRectorTest
 */
final class RenameParamToMatchTypeRector extends AbstractRector
{
    /**
     * @var bool
     */
    private $hasChanged = false;

    /**
     * @var ExpectedNameResolver
     */
    private $expectedNameResolver;

    /**
     * @var BreakingVariableRenameGuard
     */
    private $breakingVariableRenameGuard;

    /**
     * @var MatchTypeParamRenamer
     */
    private $matchTypeParamRenamer;

    /**
     * @var ParamRenameFactory
     */
    private $paramRenameFactory;

    /**
     * @var MatchParamTypeExpectedNameResolver
     */
    private $matchParamTypeExpectedNameResolver;

    public function __construct(
        BreakingVariableRenameGuard $breakingVariableRenameGuard,
        ExpectedNameResolver $expectedNameResolver,
        MatchParamTypeExpectedNameResolver $matchParamTypeExpectedNameResolver,
        ParamRenameFactory $paramRenameFactory,
        MatchTypeParamRenamer $matchTypeParamRenamer
    ) {
        $this->expectedNameResolver = $expectedNameResolver;
        $this->breakingVariableRenameGuard = $breakingVariableRenameGuard;
        $this->paramRenameFactory = $paramRenameFactory;
        $this->matchTypeParamRenamer = $matchTypeParamRenamer;
        $this->matchParamTypeExpectedNameResolver = $matchParamTypeExpectedNameResolver;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Rename variable to match new ClassType', [
            new CodeSample(
                <<<'CODE_SAMPLE'
final class SomeClass
{
    public function run(Apple $pie)
    {
        $food = $pie;
    }
}
CODE_SAMPLE
,
                <<<'CODE_SAMPLE'
final class SomeClass
{
    public function run(Apple $apple)
    {
        $food = $apple;
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
        return [ClassMethod::class];
    }

    /**
     * @param ClassMethod $node
     */
    public function refactor(Node $node): ?Node
    {
        foreach ($node->params as $param) {
            $expectedName = $this->expectedNameResolver->resolveForParamIfNotYet($param);
            if ($expectedName === null) {
                continue;
            }

            if ($this->shouldSkipParam($param, $expectedName, $node)) {
                continue;
            }

            $paramRename = $this->paramRenameFactory->create($param, $this->matchParamTypeExpectedNameResolver);
            if ($paramRename === null) {
                continue;
            }

            if ($this->matchTypeParamRenamer->rename($paramRename) === null) {
                continue;
            }

            $this->hasChanged = true;
        }

        if (! $this->hasChanged) {
            return null;
        }

        return $node;
    }

    private function shouldSkipParam(Param $param, string $expectedName, ClassMethod $classMethod): bool
    {
        /** @var string $paramName */
        $paramName = $this->getName($param);

        return $this->breakingVariableRenameGuard->shouldSkipParam($paramName, $expectedName, $classMethod, $param);
    }
}
