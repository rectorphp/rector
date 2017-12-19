<?php declare(strict_types=1);

namespace Rector\Rector\Contrib\PHPUnit;

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
 * - $this->assertTrue(is_{internal_type}($anything));
 * - $this->assertFalse(is_{internal_type}($anything));
 *
 * After:
 * - $this->assertInternalType({internal_type}, $anything);
 * - $this->assertNotInternalType({internal_type}, $anything);
 */
final class SpecificMethodInternalTypeRector extends AbstractRector
{
    /**
     * @var string[]
     */
    private $oldMethodsToTypes = [
        'is_array' => 'array',
        'is_bool' => 'bool',
        'is_callable' => 'callable',
        'is_float' => 'float',
        'is_integer' => 'integer',
        'is_numeric' => 'numeric',
        'is_object' => 'object',
        'is_resource' => 'resource',
        'is_scalar' => 'scalar',
        'is_string' => 'string',
    ];

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

        return isset($this->oldMethodsToTypes[$methodName]);
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

        $isFunctionName = $isFunctionNode->name->toString();
        $argument = $isFunctionNode->args[0]->value;

        $methodCallNode->args = [
            new Arg(new String_(
                $this->oldMethodsToTypes[$isFunctionName]
            )),
            new Arg($argument),
        ];

        return $methodCallNode;
    }
}
