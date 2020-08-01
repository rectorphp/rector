<?php

declare(strict_types=1);

namespace Rector\MagicDisclosure\Rector\MethodCall;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Expr\Variable;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;
use Rector\MagicDisclosure\NodeAnalyzer\ChainMethodCallNodeAnalyzer;
use Rector\MagicDisclosure\NodeAnalyzer\NewChainMethodCallNodeAnalyzer;
use Rector\MagicDisclosure\NodeFactory\NonFluentMethodCallFactory;
use Rector\NetteKdyby\Naming\VariableNaming;

/**
 * @sponsor Thanks https://amateri.com for sponsoring this rule - visit them on https://www.startupjobs.cz/startup/scrumworks-s-r-o
 *
 * @see \Rector\MagicDisclosure\Tests\Rector\MethodCall\MethodCallOnSetterMethodCallToStandaloneAssignRector\MethodCallOnSetterMethodCallToStandaloneAssignRectorTest
 */
final class MethodCallOnSetterMethodCallToStandaloneAssignRector extends AbstractRector
{
    /**
     * @var VariableNaming
     */
    private $variableNaming;

    /**
     * @var ChainMethodCallNodeAnalyzer
     */
    private $chainMethodCallNodeAnalyzer;

    /**
     * @var NonFluentMethodCallFactory
     */
    private $nonFluentMethodCallFactory;

    /**
     * @var NewChainMethodCallNodeAnalyzer
     */
    private $newChainMethodCallNodeAnalyzer;

    public function __construct(
        VariableNaming $variableNaming,
        ChainMethodCallNodeAnalyzer $chainMethodCallNodeAnalyzer,
        NonFluentMethodCallFactory $nonFluentMethodCallFactory,
        NewChainMethodCallNodeAnalyzer $newChainMethodCallNodeAnalyzer
    ) {
        $this->variableNaming = $variableNaming;
        $this->chainMethodCallNodeAnalyzer = $chainMethodCallNodeAnalyzer;
        $this->nonFluentMethodCallFactory = $nonFluentMethodCallFactory;
        $this->newChainMethodCallNodeAnalyzer = $newChainMethodCallNodeAnalyzer;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Change method call on setter to standalone assign before the setter', [
            new CodeSample(
                <<<'PHP'
class SomeClass
{
    public function some()
    {
        $this->anotherMethod(new AnotherClass())
            ->someFunction();
    }

    public function anotherMethod(AnotherClass $anotherClass)
    {
    }
}
PHP
,
                <<<'PHP'
class SomeClass
{
    public function some()
    {
        $anotherClass = new AnotherClass();
        $anotherClass->someFunction();
        $this->anotherMethod($anotherClass);
    }

    public function anotherMethod(AnotherClass $anotherClass)
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
        return [MethodCall::class];
    }

    /**
     * @param MethodCall $node
     */
    public function refactor(Node $node): ?Node
    {
        if ($this->shouldSkip($node)) {
            return null;
        }

        $rootMethodCall = $this->chainMethodCallNodeAnalyzer->resolveRootMethodCall($node);
        if ($rootMethodCall === null) {
            return null;
        }

        $new = $this->newChainMethodCallNodeAnalyzer->matchNewInFluentSetterMethodCall($rootMethodCall);
        if ($new === null) {
            return null;
        }

        $newStmts = $this->nonFluentMethodCallFactory->createFromNewAndRootMethodCall($new, $node);
        $this->addNodesBeforeNode($newStmts, $node);

        // change new arg to root variable
        $newVariable = $this->crateVariableFromNew($new);
        $rootMethodCall->args = [new Arg($newVariable)];

        return $rootMethodCall;
    }

    /**
     * @duplicated
     */
    private function shouldSkip(MethodCall $methodCall): bool
    {
        if (! $methodCall->var instanceof MethodCall) {
            return true;
        }

        return ! $this->chainMethodCallNodeAnalyzer->isLastChainMethodCall($methodCall);
    }

    private function crateVariableFromNew(New_ $new): Variable
    {
        $variableName = $this->variableNaming->resolveFromNode($new);
        return new Variable($variableName);
    }
}
