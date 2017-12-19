<?php declare(strict_types=1);

namespace Rector\Rector\Contrib\PHPUnit;

use Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Identifier;
use PhpParser\Node\Scalar\String_;
use Rector\NodeAnalyzer\MethodCallAnalyzer;
use Rector\Rector\AbstractRector;

/**
 * Before:
 * - $this->assertTrue(is_string($anything));
 * - $this->assertFalse(is_string($anything));
 *
 * After:
 * - $this->assertInternalType('string', $anything);
 * - $this->assertNotInternalType('string', $anything);
 */
final class SpecificMethodInternalTypeRector extends AbstractRector
{
    /**
     * @var MethodCallAnalyzer
     */
    private $methodCallAnalyzer;

    public function __construct(MethodCallAnalyzer $methodCallAnalyzer)
    {
        $this->methodCallAnalyzer = $methodCallAnalyzer;
    }

    public function isCandidate(Node $node): bool
    {
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
        if (! $firstArgumentValue instanceof FuncCall) {
            return false;
        }

        $methodName = $firstArgumentValue->name->toString();

        return Strings::startsWith($methodName, 'is_');
    }

    /**
     * @param MethodCall $methodCallNode
     */
    public function refactor(Node $methodCallNode): ?Node
    {
        $assertionMethodName = $methodCallNode->name->toString();

        if ($assertionMethodName === 'assertTrue') {
            $methodCallNode->name = new Identifier('assertInternalType');
        } else {
            $methodCallNode->name = new Identifier('assertNotInternalType');
        }

        /** @var FuncCall $methodCallNode */
        $isFunctionNode = $methodCallNode->args[0]->value;

        $argument = $isFunctionNode->args[0]->value;

        $methodCallNode->args = [
            new Arg(new String_('string')),
            new Arg($argument),
        ];

        return $methodCallNode;
    }
}
