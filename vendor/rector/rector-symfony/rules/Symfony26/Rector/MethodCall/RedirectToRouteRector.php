<?php

declare (strict_types=1);
namespace Rector\Symfony\Symfony26\Rector\MethodCall;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\MethodCall;
use Rector\PhpParser\Node\Value\ValueResolver;
use Rector\Rector\AbstractRector;
use Rector\Symfony\TypeAnalyzer\ControllerAnalyzer;
use RectorPrefix202409\Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Symfony\Tests\Symfony26\Rector\MethodCall\RedirectToRouteRector\RedirectToRouteRectorTest
 */
final class RedirectToRouteRector extends AbstractRector
{
    /**
     * @readonly
     * @var \Rector\Symfony\TypeAnalyzer\ControllerAnalyzer
     */
    private $controllerAnalyzer;
    /**
     * @readonly
     * @var \Rector\PhpParser\Node\Value\ValueResolver
     */
    private $valueResolver;
    public function __construct(ControllerAnalyzer $controllerAnalyzer, ValueResolver $valueResolver)
    {
        $this->controllerAnalyzer = $controllerAnalyzer;
        $this->valueResolver = $valueResolver;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Turns redirect to route to short helper method in Controller in Symfony', [new CodeSample('$this->redirect($this->generateUrl("homepage"));', '$this->redirectToRoute("homepage");')]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [MethodCall::class];
    }
    /**
     * @param MethodCall $node
     */
    public function refactor(Node $node) : ?Node
    {
        if (!$this->controllerAnalyzer->isInsideController($node)) {
            return null;
        }
        if (!$this->isName($node->name, 'redirect')) {
            return null;
        }
        if (!isset($node->args[0])) {
            return null;
        }
        $firstArg = $node->args[0];
        if (!$firstArg instanceof Arg) {
            return null;
        }
        $argumentValue = $firstArg->value;
        if (!$argumentValue instanceof MethodCall) {
            return null;
        }
        if (!$this->isName($argumentValue->name, 'generateUrl')) {
            return null;
        }
        if (!$this->isName($argumentValue->var, 'this')) {
            return null;
        }
        if (!$this->isDefaultReferenceType($argumentValue)) {
            return null;
        }
        return $this->nodeFactory->createMethodCall('this', 'redirectToRoute', $this->resolveArguments($node));
    }
    private function isDefaultReferenceType(MethodCall $methodCall) : bool
    {
        if (!isset($methodCall->args[2])) {
            return \true;
        }
        $refTypeArg = $methodCall->args[2];
        if (!$refTypeArg instanceof Arg) {
            return \false;
        }
        return $this->valueResolver->isValue($refTypeArg->value, UrlGeneratorInterface::ABSOLUTE_PATH);
    }
    /**
     * @return mixed[]
     */
    private function resolveArguments(MethodCall $methodCall) : array
    {
        $firstArg = $methodCall->args[0];
        if (!$firstArg instanceof Arg) {
            return [];
        }
        $generateUrlNode = $firstArg->value;
        if (!$generateUrlNode instanceof MethodCall) {
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
