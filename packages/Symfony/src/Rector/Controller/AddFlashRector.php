<?php declare(strict_types=1);

namespace Rector\Symfony\Rector\Controller;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use Rector\Node\MethodCallNodeFactory;
use Rector\NodeAnalyzer\ChainMethodCallAnalyzer;
use Rector\NodeTypeResolver\Node\Attribute;
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

    /**
     * @var string
     */
    private $controllerClass;

    public function __construct(
        MethodCallNodeFactory $methodCallNodeFactory,
        ChainMethodCallAnalyzer $chainMethodCallAnalyzer,
        string $controllerClass = 'Symfony\Bundle\FrameworkBundle\Controller\Controller'
    ) {
        $this->methodCallNodeFactory = $methodCallNodeFactory;
        $this->chainMethodCallAnalyzer = $chainMethodCallAnalyzer;
        $this->controllerClass = $controllerClass;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Turns long flash adding to short helper method in Controller in Symfony', [
            new CodeSample(
                <<<'CODE_SAMPLE'
class SomeController extends Controller
{
    public function some(Request $request)
    {
        $request->getSession()->getFlashBag()->add("success", "something");
    }
}
CODE_SAMPLE
                ,
                <<<'CODE_SAMPLE'
class SomeController extends Controller
{
    public function some(Request $request)
    {
        $this->addFlash("success", "something");
    }
}
CODE_SAMPLE
            ),
        ]);
    }

    public function isCandidate(Node $node): bool
    {
        $parentClassName = $node->getAttribute(Attribute::PARENT_CLASS_NAME);
        if ($parentClassName !== $this->controllerClass) {
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
