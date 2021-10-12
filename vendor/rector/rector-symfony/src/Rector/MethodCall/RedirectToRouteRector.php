<?php

declare (strict_types=1);
namespace Rector\Symfony\Rector\MethodCall;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\MethodCall;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ClassReflection;
use Rector\Core\Rector\AbstractRector;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Symfony\Tests\Rector\MethodCall\RedirectToRouteRector\RedirectToRouteRectorTest
 */
final class RedirectToRouteRector extends \Rector\Core\Rector\AbstractRector
{
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Turns redirect to route to short helper method in Controller in Symfony', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample('$this->redirect($this->generateUrl("homepage"));', '$this->redirectToRoute("homepage");')]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [\PhpParser\Node\Expr\MethodCall::class];
    }
    /**
     * @param MethodCall $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        $scope = $node->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::SCOPE);
        if (!$scope instanceof \PHPStan\Analyser\Scope) {
            return null;
        }
        $classReflection = $scope->getClassReflection();
        if (!$classReflection instanceof \PHPStan\Reflection\ClassReflection) {
            return null;
        }
        if (!$classReflection->isSubclassOf('Symfony\\Bundle\\FrameworkBundle\\Controller\\Controller') && !$classReflection->isSubclassOf('Symfony\\Bundle\\FrameworkBundle\\Controller\\AbstractController')) {
            return null;
        }
        if (!$this->isName($node->name, 'redirect')) {
            return null;
        }
        if (!isset($node->args[0])) {
            return null;
        }
        $firstArg = $node->args[0];
        if (!$firstArg instanceof \PhpParser\Node\Arg) {
            return null;
        }
        $argumentValue = $firstArg->value;
        if (!$argumentValue instanceof \PhpParser\Node\Expr\MethodCall) {
            return null;
        }
        if (!$this->isName($argumentValue->name, 'generateUrl')) {
            return null;
        }
        return $this->nodeFactory->createMethodCall('this', 'redirectToRoute', $this->resolveArguments($node));
    }
    /**
     * @return mixed[]
     */
    private function resolveArguments(\PhpParser\Node\Expr\MethodCall $methodCall) : array
    {
        $firstArg = $methodCall->args[0];
        if (!$firstArg instanceof \PhpParser\Node\Arg) {
            return [];
        }
        $generateUrlNode = $firstArg->value;
        if (!$generateUrlNode instanceof \PhpParser\Node\Expr\MethodCall) {
            return [];
        }
        $arguments = [];
        $arguments[] = $generateUrlNode->args[0];
        if (isset($generateUrlNode->args[1])) {
            $arguments[] = $generateUrlNode->args[1];
        }
        if (!isset($generateUrlNode->args[1]) && isset($methodCall->args[1])) {
            $arguments[] = [];
        }
        if (isset($methodCall->args[1])) {
            $arguments[] = $methodCall->args[1];
        }
        return $arguments;
    }
}
