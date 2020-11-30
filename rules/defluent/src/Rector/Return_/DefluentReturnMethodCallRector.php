<?php

declare(strict_types=1);

namespace Rector\Defluent\Rector\Return_;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt\Return_;
use Rector\Defluent\Rector\AbstractFluentChainMethodCallRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @sponsor Thanks https://amateri.com for sponsoring this rule - visit them on https://www.startupjobs.cz/startup/scrumworks-s-r-o
 *
 * @see \Rector\Defluent\Tests\Rector\Return_\DefluentReturnMethodCallRector\DefluentReturnMethodCallRectorTest
 */
final class DefluentReturnMethodCallRector extends AbstractFluentChainMethodCallRector
{
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Turns return of fluent, to standalone call line and return of value',
            [
                new CodeSample(<<<'CODE_SAMPLE'
$someClass = new SomeClass();
return $someClass->someFunction();
CODE_SAMPLE
                , <<<'CODE_SAMPLE'
$someClass = new SomeClass();
$someClass->someFunction();
return $someClass;
CODE_SAMPLE
        ),

            ]);
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [Return_::class];
    }

    /**
     * @param Return_ $node
     */
    public function refactor(Node $node): ?Node
    {
        if (! $node->expr instanceof MethodCall) {
            return null;
        }

        $methodCall = $node->expr;
        if (! $methodCall->var instanceof Variable) {
            return null;
        }

        if (! $this->fluentChainMethodCallNodeAnalyzer->isFluentClassMethodOfMethodCall($methodCall)) {
            return null;
        }

        $variableReturn = new Return_($methodCall->var);
        $this->addNodesAfterNode([$methodCall, $variableReturn], $node);

        $this->removeNode($node);

        return null;
    }
}
