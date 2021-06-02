<?php

declare(strict_types=1);

namespace Rector\Naming\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\ClassMethod;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\ValueObject\MethodName;
use Rector\Naming\ExpectedNameResolver\MatchParamTypeExpectedNameResolver;
use Rector\Naming\Guard\BreakingVariableRenameGuard;
use Rector\Naming\Naming\ExpectedNameResolver;
use Rector\Naming\ParamRenamer\ParamRenamer;
use Rector\Naming\ValueObject\ParamRename;
use Rector\Naming\ValueObjectFactory\ParamRenameFactory;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @see \Rector\Tests\Naming\Rector\ClassMethod\RenameParamToMatchTypeRector\RenameParamToMatchTypeRectorTest
 */
final class RenameParamToMatchTypeRector extends AbstractRector
{
    private bool $hasChanged = false;

    public function __construct(
        private BreakingVariableRenameGuard $breakingVariableRenameGuard,
        private ExpectedNameResolver $expectedNameResolver,
        private MatchParamTypeExpectedNameResolver $matchParamTypeExpectedNameResolver,
        private ParamRenameFactory $paramRenameFactory,
        private ParamRenamer $paramRenamer
    ) {
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Rename param to match ClassType', [
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
     * @return array<class-string<Node>>
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
        $this->hasChanged = false;

        foreach ($node->params as $param) {
            $expectedName = $this->expectedNameResolver->resolveForParamIfNotYet($param);
            if ($expectedName === null) {
                continue;
            }

            if ($this->shouldSkipParam($param, $expectedName, $node)) {
                continue;
            }

            $expectedName = $this->matchParamTypeExpectedNameResolver->resolve($param);
            if ($expectedName === null) {
                continue;
            }

            $paramRename = $this->paramRenameFactory->createFromResolvedExpectedName($param, $expectedName);
            if (! $paramRename instanceof ParamRename) {
                continue;
            }

            $this->paramRenamer->rename($paramRename);
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

        if ($this->breakingVariableRenameGuard->shouldSkipParam($paramName, $expectedName, $classMethod, $param)) {
            return true;
        }

        // promoted property
        if (! $this->isName($classMethod, MethodName::CONSTRUCT)) {
            return false;
        }

        return $param->flags !== 0;
    }
}
