<?php declare(strict_types=1);

namespace Rector\Symfony\Rector\Controller;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use Rector\Node\MethodCallNodeFactory;
use Rector\NodeTypeResolver\Node\Attribute;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;

final class RedirectToRouteRector extends AbstractRector
{
    /**
     * @var MethodCallNodeFactory
     */
    private $methodCallNodeFactory;

    /**
     * @var string
     */
    private $controllerClass;

    public function __construct(
        MethodCallNodeFactory $methodCallNodeFactory,
        string $controllerClass = 'Symfony\Bundle\FrameworkBundle\Controller\Controller'
    ) {
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
        $parentClassName = $node->getAttribute(Attribute::PARENT_CLASS_NAME);
        if ($parentClassName !== $this->controllerClass) {
            return null;
        }
        if (! $this->isName($node, 'redirect')) {
            return null;
        }

        if (! isset($node->args[0])) {
            return null;
        }

        if (! $this->isName($node->args[0]->value, 'generateUrl')) {
            return null;
        }

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
