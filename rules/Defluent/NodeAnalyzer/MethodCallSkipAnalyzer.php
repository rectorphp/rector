<?php

declare (strict_types=1);
namespace Rector\Defluent\NodeAnalyzer;

use PhpParser\Node;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\Cast;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\Return_;
use Rector\Defluent\Skipper\FluentMethodCallSkipper;
use Rector\NodeTypeResolver\Node\AttributeKey;
final class MethodCallSkipAnalyzer
{
    /**
     * @var \Rector\Defluent\Skipper\FluentMethodCallSkipper
     */
    private $fluentMethodCallSkipper;
    /**
     * @var \Rector\Defluent\NodeAnalyzer\FluentChainMethodCallNodeAnalyzer
     */
    private $fluentChainMethodCallNodeAnalyzer;
    public function __construct(\Rector\Defluent\Skipper\FluentMethodCallSkipper $fluentMethodCallSkipper, \Rector\Defluent\NodeAnalyzer\FluentChainMethodCallNodeAnalyzer $fluentChainMethodCallNodeAnalyzer)
    {
        $this->fluentMethodCallSkipper = $fluentMethodCallSkipper;
        $this->fluentChainMethodCallNodeAnalyzer = $fluentChainMethodCallNodeAnalyzer;
    }
    public function shouldSkipMethodCallIncludingNew(\PhpParser\Node\Expr\MethodCall $methodCall) : bool
    {
        if ($this->fluentMethodCallSkipper->shouldSkipRootMethodCall($methodCall)) {
            return \true;
        }
        $chainRootExpr = $this->fluentChainMethodCallNodeAnalyzer->resolveRootExpr($methodCall);
        return $chainRootExpr instanceof \PhpParser\Node\Expr\New_;
    }
    public function shouldSkipLastCallNotReturnThis(\PhpParser\Node\Expr\MethodCall $methodCall) : bool
    {
        return !$this->fluentChainMethodCallNodeAnalyzer->isMethodCallReturnThis($methodCall);
    }
    /**
     * @param \PhpParser\Node\Expr\MethodCall|\PhpParser\Node\Expr\Cast $node
     */
    public function shouldSkipDependsWithOtherExpr($node) : bool
    {
        $parentNode = $node->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::PARENT_NODE);
        if (!$parentNode instanceof \PhpParser\Node) {
            return \false;
        }
        if ($parentNode instanceof \PhpParser\Node\Stmt\Return_) {
            return \false;
        }
        if ($parentNode instanceof \PhpParser\Node\Expr\Assign) {
            return !$parentNode->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::PARENT_NODE) instanceof \PhpParser\Node\Stmt\Expression;
        }
        if ($parentNode instanceof \PhpParser\Node\Expr\Cast) {
            return $this->shouldSkipDependsWithOtherExpr($parentNode);
        }
        return !$parentNode instanceof \PhpParser\Node\Stmt\Expression;
    }
}
