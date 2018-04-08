<?php declare(strict_types=1);

namespace Rector\Rector\Contrib\Symfony\Controller;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use Rector\Node\Attribute;
use Rector\Node\MethodCallNodeFactory;
use Rector\NodeAnalyzer\ChainMethodCallAnalyzer;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;

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

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Turns long flash adding to short helper method in Controller in Symfony', [
            new CodeSample(
                '$request->getSession()->getFlashBag()->add("success", "something");',
                '$this->addflash("success", "something");'
            ),
        ]);
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

    /**
     * @param MethodCall $node
     */
    public function refactor(Node $node): ?Node
    {
        return $this->methodCallNodeFactory->createWithVariableNameMethodNameAndArguments(
            'this',
            'addFlash',
            $node->args
        );
    }
}
