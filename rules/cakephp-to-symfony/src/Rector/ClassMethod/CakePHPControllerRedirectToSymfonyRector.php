<?php

declare(strict_types=1);

namespace Rector\CakePHPToSymfony\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Identifier;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\Return_;
use Rector\CakePHPToSymfony\Rector\AbstractCakePHPRector;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;
use Rector\Core\Util\StaticRectorStrings;
use Rector\NodeTypeResolver\Node\AttributeKey;

/**
 * @see https://book.cakephp.org/2/en/controllers.html#flow-control
 * @see https://symfony.com/doc/current/controller.html#redirecting
 *
 * @see \Rector\CakePHPToSymfony\Tests\Rector\ClassMethod\CakePHPControllerRedirectToSymfonyRector\CakePHPControllerRedirectToSymfonyRectorTest
 */
final class CakePHPControllerRedirectToSymfonyRector extends AbstractCakePHPRector
{
    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Migrate CakePHP 2.4 Controller redirect() to Symfony 5', [
            new CodeSample(
                <<<'PHP'
class RedirectController extends \AppController
{
    public function index()
    {
        $this->redirect('boom');
    }
}
PHP
,
                <<<'PHP'
class RedirectController extends \AppController
{
    public function index()
    {
        return $this->redirect('boom');
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
        return [ClassMethod::class];
    }

    /**
     * @param ClassMethod $node
     */
    public function refactor(Node $node): ?Node
    {
        if (! $this->isInCakePHPController($node)) {
            return null;
        }

        $this->traverseNodesWithCallable($node, function (Node $node) {
            if ($node instanceof Return_) {
                $returnedExpr = $node->expr;
                if ($returnedExpr === null) {
                    return null;
                }

                return $this->refactorRedirectMethodCall($returnedExpr);
            }

            if ($node instanceof Expression) {
                return $this->refactorRedirectMethodCall($node);
            }

            return null;
        });

        return $node;
    }

    /**
     * @param Expr|Expression $expr
     */
    private function refactorRedirectMethodCall($expr): ?Return_
    {
        if ($expr instanceof Expression) {
            $expr = $expr->expr;
        }

        if (! $expr instanceof MethodCall) {
            return null;
        }

        if (! $this->isName($expr->var, 'this')) {
            return null;
        }

        if (! $this->isName($expr->name, 'redirect')) {
            return null;
        }

        $this->refactorRedirectArgs($expr);

        $parentNode = $expr->getAttribute(AttributeKey::PARENT_NODE);
        if ($parentNode instanceof Return_) {
            return null;
        }

        // add "return"
        return new Return_($expr);
    }

    private function refactorRedirectArgs(MethodCall $methodCall): void
    {
        $argumentValue = $methodCall->args[0]->value;
        if ($argumentValue instanceof String_) {
            return;
        }

        // not sure what to do
        if (! $argumentValue  instanceof Array_) {
            return;
        }

        $argumentValue = $this->getValue($argumentValue);

        if (! isset($argumentValue['controller']) || ! isset($argumentValue['action'])) {
            return;
        }

        $composedRouteName = $argumentValue['controller'] . '_' . $argumentValue['action'];
        $composedRouteName = StaticRectorStrings::camelCaseToUnderscore($composedRouteName);

        $methodCall->args[0]->value = new String_($composedRouteName);
        $methodCall->name = new Identifier('redirectToRoute');
    }
}
