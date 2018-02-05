<?php declare(strict_types=1);

namespace Rector\Rector\Contrib\Symfony\Controller;

use PhpParser\Node;
use Rector\Node\Attribute;
use Rector\Node\MethodCallNodeFactory;
use Rector\NodeAnalyzer\ChainMethodCallAnalyzer;
use Rector\Rector\AbstractRector;

/**
 * Before:
 * - $request->getSession()->getFlashBag()->add('success', 'something');
 *
 * After:
 * - $this->addflash('success', 'something');
 */
final class AddFlashRector extends AbstractRector
{
    /**
     * @var MethodCallNodeFactory
     */
    private $methodCallNodeFactory;

    /**
     * @var ChainMethodCallAnalyzer
     */
    private $chainMethodCallAnalyzer;

    public function __construct(
        MethodCallNodeFactory $methodCallNodeFactory,
        ChainMethodCallAnalyzer $chainMethodCallAnalyzer
    ) {
        $this->methodCallNodeFactory = $methodCallNodeFactory;
        $this->chainMethodCallAnalyzer = $chainMethodCallAnalyzer;
    }

    public function isCandidate(Node $node): bool
    {
        $parentClassName = $node->getAttribute(Attribute::PARENT_CLASS_NAME);
        if ($parentClassName !== 'Symfony\Bundle\FrameworkBundle\Controller\Controller') {
            return false;
        }

        if (! $this->chainMethodCallAnalyzer->isTypeAndChainCalls(
            $node,
            'Symfony\Component\HttpFoundation\Request',
            ['getSession', 'getFlashBag', 'add']
        )
        ) {
            return false;
        }

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
