<?php

declare(strict_types=1);

namespace Rector\Symfony2\Rector\MethodCall;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PHPStan\Type\ObjectType;
use Rector\Core\Rector\AbstractRector;
use Rector\Defluent\NodeAnalyzer\FluentChainMethodCallNodeAnalyzer;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @see \Rector\Symfony2\Tests\Rector\MethodCall\AddFlashRector\AddFlashRectorTest
 */
final class AddFlashRector extends AbstractRector
{
    /**
     * @var FluentChainMethodCallNodeAnalyzer
     */
    private $fluentChainMethodCallNodeAnalyzer;

    public function __construct(FluentChainMethodCallNodeAnalyzer $fluentChainMethodCallNodeAnalyzer)
    {
        $this->fluentChainMethodCallNodeAnalyzer = $fluentChainMethodCallNodeAnalyzer;
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Turns long flash adding to short helper method in Controller in Symfony',
            [
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
        $parentClassName = $node->getAttribute(AttributeKey::PARENT_CLASS_NAME);
        if ($parentClassName !== Controller::class) {
            return null;
        }

        if (! $this->fluentChainMethodCallNodeAnalyzer->isTypeAndChainCalls(
            $node,
            new ObjectType(Request::class),
            ['getSession', 'getFlashBag', 'add']
        )
        ) {
            return null;
        }

        return $this->nodeFactory->createMethodCall('this', 'addFlash', $node->args);
    }
}
