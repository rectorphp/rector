<?php

declare(strict_types=1);

namespace Rector\Symfony\Rector\Controller;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PHPStan\Type\ObjectType;
use Rector\Core\PhpParser\Node\Manipulator\ChainMethodCallManipulator;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

/**
 * @see \Rector\Symfony\Tests\Rector\Controller\AddFlashRector\AddFlashRectorTest
 */
final class AddFlashRector extends AbstractRector
{
    /**
     * @var ChainMethodCallManipulator
     */
    private $chainMethodCallManipulator;

    public function __construct(ChainMethodCallManipulator $chainMethodCallManipulator)
    {
        $this->chainMethodCallManipulator = $chainMethodCallManipulator;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Turns long flash adding to short helper method in Controller in Symfony', [
            new CodeSample(
                <<<'PHP'
class SomeController extends Controller
{
    public function some(Request $request)
    {
        $request->getSession()->getFlashBag()->add("success", "something");
    }
}
PHP
                ,
                <<<'PHP'
class SomeController extends Controller
{
    public function some(Request $request)
    {
        $this->addFlash("success", "something");
    }
}
PHP
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

        if (! $this->chainMethodCallManipulator->isTypeAndChainCalls(
            $node,
            new ObjectType(Request::class),
            ['getSession', 'getFlashBag', 'add']
        )
        ) {
            return null;
        }

        return $this->createMethodCall('this', 'addFlash', $node->args);
    }
}
