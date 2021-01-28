<?php

declare(strict_types=1);

namespace Rector\Symfony5\NodeFactory;

use PhpParser\Node;
use PhpParser\Node\Expr\BinaryOp\NotIdentical;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\If_;
use PhpParser\Node\Stmt\Return_;
use Rector\Core\PhpParser\Node\NodeFactory;
use Rector\NodeNameResolver\NodeNameResolver;
use Symplify\Astral\NodeTraverser\SimpleCallableNodeTraverser;

final class OnSuccessLogoutClassMethodFactory
{
    /**
     * @var string
     */
    private const LOGOUT_EVENT = 'logoutEvent';

    /**
     * @var NodeFactory
     */
    private $nodeFactory;

    /**
     * @var NodeNameResolver
     */
    private $nodeNameResolver;

    /**
     * @var SimpleCallableNodeTraverser
     */
    private $simpleCallableNodeTraverser;

    /**
     * @var BareLogoutClassMethodFactory
     */
    private $bareLogoutClassMethodFactory;

    public function __construct(
        NodeFactory $nodeFactory,
        NodeNameResolver $nodeNameResolver,
        SimpleCallableNodeTraverser $simpleCallableNodeTraverser,
        BareLogoutClassMethodFactory $bareLogoutClassMethodFactory
    ) {
        $this->nodeFactory = $nodeFactory;
        $this->nodeNameResolver = $nodeNameResolver;
        $this->simpleCallableNodeTraverser = $simpleCallableNodeTraverser;
        $this->bareLogoutClassMethodFactory = $bareLogoutClassMethodFactory;
    }

    public function createFromOnLogoutSuccessClassMethod(ClassMethod $onLogoutSuccessClassMethod): ClassMethod
    {
        $classMethod = $this->bareLogoutClassMethodFactory->create();

        $getResponseMethodCall = new MethodCall(new Variable(self::LOGOUT_EVENT), 'getResponse');
        $notIdentical = new NotIdentical($getResponseMethodCall, $this->nodeFactory->createNull());

        $if = new If_($notIdentical);
        $if->stmts[] = new Return_();

        // replace `return $response;` with `$logoutEvent->setResponse($response)`
        $this->replaceReturnResponseWithSetResponse($onLogoutSuccessClassMethod);
        $this->replaceRequestWithGetRequest($onLogoutSuccessClassMethod);

        $oldClassStmts = (array) $onLogoutSuccessClassMethod->stmts;
        $classStmts = array_merge([$if], $oldClassStmts);
        $classMethod->stmts = $classStmts;

        return $classMethod;
    }

    private function replaceReturnResponseWithSetResponse(ClassMethod $classMethod): void
    {
        $this->simpleCallableNodeTraverser->traverseNodesWithCallable($classMethod, function (Node $node): ?Expression {
            if (! $node instanceof Return_) {
                return null;
            }

            if ($node->expr === null) {
                return null;
            }

            $args = $this->nodeFactory->createArgs([$node->expr]);
            $methodCall = new MethodCall(new Variable(self::LOGOUT_EVENT), 'setResponse', $args);

            return new Expression($methodCall);
        });
    }

    private function replaceRequestWithGetRequest(ClassMethod $classMethod): void
    {
        $this->simpleCallableNodeTraverser->traverseNodesWithCallable($classMethod, function (Node $node): ?MethodCall {
            if (! $node instanceof Variable) {
                return null;
            }

            if (! $this->nodeNameResolver->isName($node, 'request')) {
                return null;
            }

            return new MethodCall(new Variable(self::LOGOUT_EVENT), 'getRequest');
        });
    }
}
