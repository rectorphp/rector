<?php

declare (strict_types=1);
namespace Rector\Laravel\Rector\MethodCall;

use PhpParser\Node;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Name;
use Rector\Core\Rector\AbstractRector;
use Rector\Defluent\NodeAnalyzer\FluentChainMethodCallNodeAnalyzer;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Laravel\Tests\Rector\MethodCall\RedirectBackToBackHelperRector\RedirectBackToBackHelperRectorTest
 */
final class RedirectBackToBackHelperRector extends \Rector\Core\Rector\AbstractRector
{
    /**
     * @readonly
     * @var \Rector\Defluent\NodeAnalyzer\FluentChainMethodCallNodeAnalyzer
     */
    private $fluentChainMethodCallNodeAnalyzer;
    public function __construct(\Rector\Defluent\NodeAnalyzer\FluentChainMethodCallNodeAnalyzer $fluentChainMethodCallNodeAnalyzer)
    {
        $this->fluentChainMethodCallNodeAnalyzer = $fluentChainMethodCallNodeAnalyzer;
    }
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Replace `redirect()->back()` and `Redirect::back()` with `back()`', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
use Illuminate\Support\Facades\Redirect;

class MyController
{
    public function store()
    {
        return redirect()->back()->with('error', 'Incorrect Details.')
    }

    public function update()
    {
        return Redirect::back()->with('error', 'Incorrect Details.')
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use Illuminate\Support\Facades\Redirect;

class MyController
{
    public function store()
    {
        return back()->with('error', 'Incorrect Details.')
    }

    public function update()
    {
        return back()->with('error', 'Incorrect Details.')
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
        return [\PhpParser\Node\Expr\MethodCall::class, \PhpParser\Node\Expr\StaticCall::class];
    }
    /**
     * @param MethodCall|StaticCall $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        if ($node instanceof \PhpParser\Node\Expr\MethodCall) {
            return $this->updateRedirectHelperCall($node);
        }
        return $this->updateRedirectStaticCall($node);
    }
    private function updateRedirectHelperCall(\PhpParser\Node\Expr\MethodCall $methodCall) : ?\PhpParser\Node\Expr\MethodCall
    {
        if (!$this->isName($methodCall->name, 'back')) {
            return null;
        }
        $rootExpr = $this->fluentChainMethodCallNodeAnalyzer->resolveRootExpr($methodCall);
        $parentNode = $rootExpr->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::PARENT_NODE);
        if (!$parentNode instanceof \PhpParser\Node\Expr\MethodCall) {
            return null;
        }
        if (!$parentNode->var instanceof \PhpParser\Node\Expr\FuncCall) {
            return null;
        }
        if ($parentNode->var->getArgs() !== []) {
            return null;
        }
        if (!$this->isName($parentNode->var->name, 'redirect')) {
            return null;
        }
        $this->removeNode($methodCall);
        $parentNode->var->name = new \PhpParser\Node\Name('back');
        return $parentNode;
    }
    private function updateRedirectStaticCall(\PhpParser\Node\Expr\StaticCall $staticCall) : ?\PhpParser\Node\Expr\FuncCall
    {
        if (!$this->isName($staticCall->class, 'Illuminate\\Support\\Facades\\Redirect')) {
            return null;
        }
        if (!$this->isName($staticCall->name, 'back')) {
            return null;
        }
        return new \PhpParser\Node\Expr\FuncCall(new \PhpParser\Node\Name('back'), $staticCall->args);
    }
}
