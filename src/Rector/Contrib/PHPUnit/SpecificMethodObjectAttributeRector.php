<?php declare(strict_types=1);

namespace Rector\Rector\Contrib\PHPUnit;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\Isset_;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Identifier;
use PhpParser\Node\Scalar\String_;
use Rector\NodeAnalyzer\MethodCallAnalyzer;
use Rector\Rector\AbstractRector;

/**
 * Before:
 * - $this->assertSame(5, count($anything));
 *
 * After:
 * - $this->assertCount(5, $anything);
 */
final class SpecificMethodObjectAttributeRector extends AbstractRector
{
    /**
     * @var MethodCallAnalyzer
     */
    private $methodCallAnalyzer;

    /**
     * @var string|null
     */
    private $activeFuncCallName;

    public function __construct(MethodCallAnalyzer $methodCallAnalyzer)
    {
        $this->methodCallAnalyzer = $methodCallAnalyzer;
    }

    public function isCandidate(Node $node): bool
    {
        $this->activeFuncCallName = null;

        if (! $this->methodCallAnalyzer->isTypesAndMethods(
            $node,
            ['PHPUnit\Framework\TestCase', 'PHPUnit_Framework_TestCase'],
            ['assertTrue', 'assertFalse']
        )) {
            return false;
        }

        /** @var MethodCall $methodCallNode */
        $methodCallNode = $node;

        $firstArgumentValue = $methodCallNode->args[0]->value;

        // is property access
        if (! $firstArgumentValue instanceof Isset_) {
            return false;
        }

        /** @var Isset_ $issetNode */
        $issetNode = $firstArgumentValue;

        return $issetNode->vars[0] instanceof PropertyFetch;
    }

    /**
     * @param MethodCall $methodCallNode
     */
    public function refactor(Node $methodCallNode): ?Node
    {
        // rename method
        if ($methodCallNode->name->toString() === 'assertTrue') {
            $methodCallNode->name = new Identifier('assertObjectHasAttribute');
        } else {
            $methodCallNode->name = new Identifier('assertObjectNotHasAttribute');
        }

        // move isset to property and object
        /** @var Isset_ $issetNode */
        $issetNode = $methodCallNode->args[0]->value;

        /** @var PropertyFetch $propertyFetchNode */
        $propertyFetchNode = $issetNode->vars[0];

        // and set as arguments
        $methodCallNode->args = [
            new Arg(new String_($propertyFetchNode->name->toString())),
            new Arg($propertyFetchNode->var),
        ];

        return $methodCallNode;
    }
}
