<?php

declare (strict_types=1);
namespace Rector\Nette\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Stmt\ClassMethod;
use Rector\Core\Rector\AbstractRector;
use Rector\Nette\NodeAnalyzer\NetteClassAnalyzer;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Nette\Tests\Rector\ClassMethod\MergeTemplateSetFileToTemplateRenderRector\MergeTemplateSetFileToTemplateRenderRectorTest
 */
final class MergeTemplateSetFileToTemplateRenderRector extends \Rector\Core\Rector\AbstractRector
{
    /**
     * @var \Rector\Nette\NodeAnalyzer\NetteClassAnalyzer
     */
    private $netteClassAnalyzer;
    public function __construct(\Rector\Nette\NodeAnalyzer\NetteClassAnalyzer $netteClassAnalyzer)
    {
        $this->netteClassAnalyzer = $netteClassAnalyzer;
    }
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Change $this->template->setFile() $this->template->render()', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
use Nette\Application\UI\Control;

final class SomeControl extends Control
{
    public function render()
    {
        $this->template->setFile(__DIR__ . '/someFile.latte');
        $this->template->render();
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use Nette\Application\UI\Control;

final class SomeControl extends Control
{
    public function render()
    {
        $this->template->render(__DIR__ . '/someFile.latte');
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
        return [\PhpParser\Node\Stmt\ClassMethod::class];
    }
    /**
     * @param ClassMethod $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        if (!$this->netteClassAnalyzer->isInComponent($node)) {
            return null;
        }
        /** @var MethodCall[] $methodCalls */
        $methodCalls = $this->betterNodeFinder->findInstanceOf((array) $node->stmts, \PhpParser\Node\Expr\MethodCall::class);
        $setFileMethodCall = $this->resolveSingleSetFileMethodCall($methodCalls);
        if (!$setFileMethodCall instanceof \PhpParser\Node\Expr\MethodCall) {
            return null;
        }
        foreach ($methodCalls as $methodCall) {
            if (!$this->isName($methodCall->name, 'render')) {
                continue;
            }
            if (isset($methodCall->args[0])) {
                continue;
            }
            $this->removeNode($setFileMethodCall);
            $methodCall->args[0] = new \PhpParser\Node\Arg($setFileMethodCall->args[0]->value);
            return $node;
        }
        return null;
    }
    /**
     * @param MethodCall[] $methodCalls
     */
    private function resolveSingleSetFileMethodCall(array $methodCalls) : ?\PhpParser\Node\Expr\MethodCall
    {
        $singleSetFileMethodCall = null;
        foreach ($methodCalls as $methodCall) {
            if (!$this->isName($methodCall->name, 'setFile')) {
                continue;
            }
            if ($singleSetFileMethodCall instanceof \PhpParser\Node\Expr\MethodCall) {
                return null;
            }
            $singleSetFileMethodCall = $methodCall;
        }
        return $singleSetFileMethodCall;
    }
}
