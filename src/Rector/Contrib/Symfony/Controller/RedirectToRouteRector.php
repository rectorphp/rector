<?php declare(strict_types=1);

namespace Rector\Rector\Contrib\Symfony\Controller;

use PhpParser\Node;
use Rector\Node\Attribute;
use Rector\Node\MethodCallNodeFactory;
use Rector\NodeAnalyzer\MethodArgumentAnalyzer;
use Rector\NodeAnalyzer\MethodCallAnalyzer;
use Rector\Rector\AbstractRector;

/**
 * Before:
 * return $this->redirect($this->generateUrl("homepage"));
 * After:
 * return $this->redirectToRoute("homepage");
 */
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

    public function __construct(
        MethodCallAnalyzer $methodCallAnalyzer,
        MethodArgumentAnalyzer $methodArgumentAnalyzer,
        MethodCallNodeFactory $methodCallNodeFactory
    ) {
        $this->methodCallAnalyzer = $methodCallAnalyzer;
        $this->methodArgumentAnalyzer = $methodArgumentAnalyzer;
        $this->methodCallNodeFactory = $methodCallNodeFactory;
    }

    public function isCandidate(Node $node): bool
    {
        $parentClassName = $node->getAttribute(Attribute::PARENT_CLASS_NAME);
        if ($parentClassName !== 'Symfony\Bundle\FrameworkBundle\Controller\Controller') {
            return false;
        }

        if (! $this->methodCallAnalyzer->isMethod($node, 'redirect')) {
            return false;
        }

        if (! $this->methodArgumentAnalyzer->hasMethodFirstArgument($node)) {
            return false;
        }

        if (! $this->methodCallAnalyzer->isMethod($node->args[0]->value, 'generateUrl')) {
            return false;
        }

        return true;
    }

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
    private function resolveArguments(Node $node): array
    {
        $generateUrlNode = $node->args[0]->value;

        $arguments = [];
        $arguments[] = $generateUrlNode->args[0];

        if ($generateUrlNode->args[1]) {
            $arguments[] = $generateUrlNode->args[1];
        }

        if (! $generateUrlNode->args[1] && $node->args[1]) {
            $arguments[] = [];
        }

        if ($node->args[1]) {
            $arguments[] = $node->args[1];
        }

        return $arguments;
    }
}
