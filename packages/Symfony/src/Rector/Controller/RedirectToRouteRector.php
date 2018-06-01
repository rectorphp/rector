<?php declare(strict_types=1);

namespace Rector\Symfony\Rector\Controller;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use Rector\Node\Attribute;
use Rector\Node\MethodCallNodeFactory;
use Rector\NodeAnalyzer\MethodArgumentAnalyzer;
use Rector\NodeAnalyzer\MethodCallAnalyzer;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;

final class RedirectToRouteRector extends AbstractRector
{
    /**
     * @var MethodCallAnalyzer
     */
    private $methodCallAnalyzer;

    /**
     * @var MethodArgumentAnalyzer
     */
    private $methodArgumentAnalyzer;

    /**
     * @var MethodCallNodeFactory
     */
    private $methodCallNodeFactory;
    /**
     * @var string
     */
    private $controllerClass;

    public function __construct(
        MethodCallAnalyzer $methodCallAnalyzer,
        MethodArgumentAnalyzer $methodArgumentAnalyzer,
        MethodCallNodeFactory $methodCallNodeFactory,
        string $controllerClass = 'Symfony\Bundle\FrameworkBundle\Controller\Controller'
    ) {
        $this->methodCallAnalyzer = $methodCallAnalyzer;
        $this->methodArgumentAnalyzer = $methodArgumentAnalyzer;
        $this->methodCallNodeFactory = $methodCallNodeFactory;
        $this->controllerClass = $controllerClass;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Turns redirect to route to short helper method in Controller in Symfony', [
            new CodeSample(
                '$this->redirect($this->generateUrl("homepage"));',
                '$this->redirectToRoute("homepage");'
            ),
        ]);
    }

    public function isCandidate(Node $node): bool
    {
        $parentClassName = $node->getAttribute(Attribute::PARENT_CLASS_NAME);
        if ($parentClassName !== $this->controllerClass) {
            return false;
        }

        if (! $this->methodCallAnalyzer->isMethod($node, 'redirect')) {
            return false;
        }

        if (! $this->methodArgumentAnalyzer->hasMethodNthArgument($node, 1)) {
            return false;
        }

        /** @var MethodCall $methodCallNode */
        $methodCallNode = $node;

        if (! $this->methodCallAnalyzer->isMethod($methodCallNode->args[0]->value, 'generateUrl')) {
            return false;
        }

        return true;
    }

    /**
     * @param MethodCall $node
     */
    public function refactor(Node $node): ?Node
    {
        return $this->methodCallNodeFactory->createWithVariableNameMethodNameAndArguments(
            'this',
            'redirectToRoute',
            $this->resolveArguments($node)
        );
    }

    /**
     * @return mixed[]
     */
    private function resolveArguments(MethodCall $node): array
    {
        /** @var MethodCall $generateUrlNode */
        $generateUrlNode = $node->args[0]->value;

        $arguments = [];
        $arguments[] = $generateUrlNode->args[0];

        if (isset($generateUrlNode->args[1])) {
            $arguments[] = $generateUrlNode->args[1];
        }

        if (! isset($generateUrlNode->args[1]) && isset($node->args[1])) {
            $arguments[] = [];
        }

        if (isset($node->args[1])) {
            $arguments[] = $node->args[1];
        }

        return $arguments;
    }
}
