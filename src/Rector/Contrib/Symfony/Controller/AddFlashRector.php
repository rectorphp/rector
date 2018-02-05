<?php declare(strict_types=1);

namespace Rector\Rector\Contrib\Symfony\Controller;

use PhpParser\Node;
use Rector\Node\Attribute;
use Rector\Node\MethodCallNodeFactory;
use Rector\NodeAnalyzer\MethodCallAnalyzer;
use Rector\Rector\AbstractRector;

/**
 * Before:
 * $request->getSession()->getFlashBag()->add('success', 'something');
 * After:
 * $this->addflash('success', 'something');
 */
final class AddFlashRector extends AbstractRector
{
    /**
     * @var MethodCallAnalyzer
     */
    private $methodCallAnalyzer;

    /**
     * @var MethodCallNodeFactory
     */
    private $methodCallNodeFactory;

    public function __construct(
        MethodCallAnalyzer $methodCallAnalyzer,
        MethodCallNodeFactory $methodCallNodeFactory
    ) {
        $this->methodCallAnalyzer = $methodCallAnalyzer;
        $this->methodCallNodeFactory = $methodCallNodeFactory;
    }

    public function isCandidate(Node $node): bool
    {
        $parentClassName = $node->getAttribute(Attribute::PARENT_CLASS_NAME);
        if ($parentClassName !== 'Symfony\Bundle\FrameworkBundle\Controller\Controller') {
            return false;
        }

        if (! $this->methodCallAnalyzer->isTypeAndMethod(
            $node,
            'Symfony\Component\HttpFoundation\Request',
            'getSession'
        )) {
            return false;
        }

        if ($node->getAttribute(Attribute::NEXT_NODE)->name !== 'getFlashBag') {
            return false;
        }

        // add check for add method
        return true;
    }

    public function refactor(Node $node): ?Node
    {
        return $this->methodCallNodeFactory->createWithVariableNameMethodNameAndArguments(
            'this',
            'addFlash',
            [] // todo arguments of last method
        );
    }
}
