<?php

declare (strict_types=1);
namespace Rector\Symfony\NodeFactory;

use PhpParser\Node;
use PhpParser\Node\Expr\BinaryOp\NotIdentical;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\If_;
use PhpParser\Node\Stmt\Return_;
use Rector\Core\PhpParser\Node\NodeFactory;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\NodeTypeResolver\Node\AttributeKey;
use RectorPrefix20220418\Symplify\Astral\NodeTraverser\SimpleCallableNodeTraverser;
final class OnSuccessLogoutClassMethodFactory
{
    /**
     * @var string
     */
    private const LOGOUT_EVENT = 'logoutEvent';
    /**
     * @readonly
     * @var \Rector\Core\PhpParser\Node\NodeFactory
     */
    private $nodeFactory;
    /**
     * @readonly
     * @var \Rector\NodeNameResolver\NodeNameResolver
     */
    private $nodeNameResolver;
    /**
     * @readonly
     * @var \Symplify\Astral\NodeTraverser\SimpleCallableNodeTraverser
     */
    private $simpleCallableNodeTraverser;
    /**
     * @readonly
     * @var \Rector\Symfony\NodeFactory\BareLogoutClassMethodFactory
     */
    private $bareLogoutClassMethodFactory;
    public function __construct(\Rector\Core\PhpParser\Node\NodeFactory $nodeFactory, \Rector\NodeNameResolver\NodeNameResolver $nodeNameResolver, \RectorPrefix20220418\Symplify\Astral\NodeTraverser\SimpleCallableNodeTraverser $simpleCallableNodeTraverser, \Rector\Symfony\NodeFactory\BareLogoutClassMethodFactory $bareLogoutClassMethodFactory)
    {
        $this->nodeFactory = $nodeFactory;
        $this->nodeNameResolver = $nodeNameResolver;
        $this->simpleCallableNodeTraverser = $simpleCallableNodeTraverser;
        $this->bareLogoutClassMethodFactory = $bareLogoutClassMethodFactory;
    }
    public function createFromOnLogoutSuccessClassMethod(\PhpParser\Node\Stmt\ClassMethod $onLogoutSuccessClassMethod) : \PhpParser\Node\Stmt\ClassMethod
    {
        $classMethod = $this->bareLogoutClassMethodFactory->create();
        $getResponseMethodCall = new \PhpParser\Node\Expr\MethodCall(new \PhpParser\Node\Expr\Variable(self::LOGOUT_EVENT), 'getResponse');
        $notIdentical = new \PhpParser\Node\Expr\BinaryOp\NotIdentical($getResponseMethodCall, $this->nodeFactory->createNull());
        $if = new \PhpParser\Node\Stmt\If_($notIdentical);
        $if->stmts[] = new \PhpParser\Node\Stmt\Return_();
        // replace `return $response;` with `$logoutEvent->setResponse($response)`
        $this->replaceReturnResponseWithSetResponse($onLogoutSuccessClassMethod);
        $this->replaceRequestWithGetRequest($onLogoutSuccessClassMethod);
        $oldClassStmts = (array) $onLogoutSuccessClassMethod->stmts;
        $classStmts = \array_merge([$if], $oldClassStmts);
        $classMethod->stmts = $classStmts;
        return $classMethod;
    }
    private function replaceReturnResponseWithSetResponse(\PhpParser\Node\Stmt\ClassMethod $classMethod) : void
    {
        $this->simpleCallableNodeTraverser->traverseNodesWithCallable($classMethod, function (\PhpParser\Node $node) : ?Expression {
            if (!$node instanceof \PhpParser\Node\Stmt\Return_) {
                return null;
            }
            if ($node->expr === null) {
                return null;
            }
            $args = $this->nodeFactory->createArgs([$node->expr]);
            $methodCall = new \PhpParser\Node\Expr\MethodCall(new \PhpParser\Node\Expr\Variable(self::LOGOUT_EVENT), 'setResponse', $args);
            return new \PhpParser\Node\Stmt\Expression($methodCall);
        });
    }
    private function replaceRequestWithGetRequest(\PhpParser\Node\Stmt\ClassMethod $classMethod) : void
    {
        $this->simpleCallableNodeTraverser->traverseNodesWithCallable($classMethod, function (\PhpParser\Node $node) : ?MethodCall {
            if (!$node instanceof \PhpParser\Node\Expr\Variable) {
                return null;
            }
            if (!$this->nodeNameResolver->isName($node, 'request')) {
                return null;
            }
            $parent = $node->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::PARENT_NODE);
            if ($parent instanceof \PhpParser\Node\Param) {
                return null;
            }
            return new \PhpParser\Node\Expr\MethodCall(new \PhpParser\Node\Expr\Variable(self::LOGOUT_EVENT), 'getRequest');
        });
    }
}
