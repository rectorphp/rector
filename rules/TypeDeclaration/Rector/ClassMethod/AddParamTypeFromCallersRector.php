<?php

declare(strict_types=1);

namespace Rector\TypeDeclaration\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Stmt\ClassMethod;
use Rector\Core\Rector\AbstractRector;
use Rector\TypeDeclaration\NodeAnalyzer\CallTypesResolver;
use Rector\TypeDeclaration\NodeAnalyzer\ClassMethodParamTypeCompleter;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @changelog https://github.com/symplify/phpstan-rules/blob/master/docs/rules_overview.md#checktypehintcallertyperule
 *
 * @see \Rector\Tests\TypeDeclaration\Rector\ClassMethod\AddParamTypeFromCallersRector\AddParamTypeFromCallersRectorTest
 *
 * Less strict version of \Rector\TypeDeclaration\Rector\ClassMethod\AddMethodCallBasedStrictParamTypeRector,
 * that can work with docblocks too
 */
final class AddParamTypeFromCallersRector extends AbstractRector
{
    public function __construct(
        private CallTypesResolver $callTypesResolver,
        private ClassMethodParamTypeCompleter $classMethodParamTypeCompleter
    ) {
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Add param type based on called types in that particular method', [
            new CodeSample(
                <<<'CODE_SAMPLE'
final class SomeClass
{
    public function run(Return_ $return)
    {
        $this->print($return);
    }

    public function print($return)
    {
    }
}
CODE_SAMPLE
                ,
                <<<'CODE_SAMPLE'
final class SomeClass
{
    public function run(Return_ $return)
    {
        $this->print($return);
    }

    public function print(Return_ $return)
    {
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
        if ($node->params === []) {
            return null;
        }

        $calls = $this->nodeRepository->findCallsByClassMethod($node);
        if ($calls === []) {
            return null;
        }

        $classMethodParameterTypes = $this->callTypesResolver->resolveWeakTypesFromCalls($calls);
        return $this->classMethodParamTypeCompleter->complete($node, $classMethodParameterTypes);
    }
}
