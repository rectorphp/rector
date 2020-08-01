<?php

declare(strict_types=1);

namespace Rector\MagicDisclosure\Rector\Return_;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt\Return_;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;
use Rector\MagicDisclosure\Rector\MethodCall\AbstractFluentChainMethodCallRector;

/**
 * @sponsor Thanks https://amateri.com for sponsoring this rule - visit them on https://www.startupjobs.cz/startup/scrumworks-s-r-o
 *
 * @see \Rector\MagicDisclosure\Tests\Rector\Return_\DefluentReturnMethodCallRector\DefluentReturnMethodCallRectorTest
 */
final class DefluentReturnMethodCallRector extends AbstractFluentChainMethodCallRector
{
    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Turns return of fluent, to standalone call line and return of value', [
            new CodeSample(<<<'PHP'
$someClass = new SomeClass();
return $someClass->someFunction();
PHP
            , <<<'PHP'
$someClass = new SomeClass();
$someClass->someFunction();
return $someClass;
PHP
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

        $this->addNodeAfterNode($methodCall, $node);
        $this->addNodeAfterNode($variableReturn, $node);

        $this->removeNode($node);

        return null;
    }
}
