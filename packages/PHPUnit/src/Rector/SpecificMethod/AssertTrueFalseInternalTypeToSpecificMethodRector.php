<?php declare(strict_types=1);

namespace Rector\PHPUnit\Rector\SpecificMethod;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Name;
use Rector\Builder\IdentifierRenamer;
use Rector\Node\NodeFactory;
use Rector\NodeAnalyzer\MethodCallAnalyzer;
use Rector\Rector\AbstractPHPUnitRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;

final class AssertTrueFalseInternalTypeToSpecificMethodRector extends AbstractPHPUnitRector
{
    /**
     * @var string[]
     */
    private $oldMethodsToTypes = [
        'is_array' => 'array',
        'is_bool' => 'bool',
        'is_callable' => 'callable',
        'is_double' => 'double',
        'is_float' => 'float',
        'is_int' => 'int',
        'is_integer' => 'integer',
        'is_iterable' => 'iterable',
        'is_numeric' => 'numeric',
        'is_object' => 'object',
        'is_real' => 'real',
        'is_resource' => 'resource',
        'is_scalar' => 'scalar',
        'is_string' => 'string',
    ];

    /**
     * @var string[]
     */
    private $renameMethodsMap = [
        'assertTrue' => 'assertInternalType',
        'assertFalse' => 'assertNotInternalType',
    ];

    /**
     * @var MethodCallAnalyzer
     */
    private $methodCallAnalyzer;

    /**
     * @var IdentifierRenamer
     */
    private $identifierRenamer;

    /**
     * @var NodeFactory
     */
    private $nodeFactory;

    public function __construct(
        MethodCallAnalyzer $methodCallAnalyzer,
        IdentifierRenamer $identifierRenamer,
        NodeFactory $nodeFactory
    ) {
        $this->methodCallAnalyzer = $methodCallAnalyzer;
        $this->identifierRenamer = $identifierRenamer;
        $this->nodeFactory = $nodeFactory;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition(
            'Turns true/false with internal type comparisons to their method name alternatives in PHPUnit TestCase',
            [
                new CodeSample(
                    '$this->assertTrue(is_{internal_type}($anything), "message");',
                    '$this->assertInternalType({internal_type}, $anything, "message");'
                ),
                new CodeSample(
                    '$this->assertFalse(is_{internal_type}($anything), "message");',
                    '$this->assertNotInternalType({internal_type}, $anything, "message");'
                ),
            ]
        );
    }

    public function isCandidate(Node $node): bool
    {
        if (! $this->isInTestClass($node)) {
            return false;
        }

        if (! $this->methodCallAnalyzer->isMethods($node, array_keys($this->renameMethodsMap))) {
            return false;
        }

        /** @var MethodCall $methodCallNode */
        $methodCallNode = $node;

        /** @var FuncCall $firstArgumentValue */
        $firstArgumentValue = $methodCallNode->args[0]->value;
        if (! $this->isNamedFunction($firstArgumentValue)) {
            return false;
        }

        $methodName = (string) $firstArgumentValue->name;

        return isset($this->oldMethodsToTypes[$methodName]);
    }

    /**
     * @param MethodCall $methodCallNode
     */
    public function refactor(Node $methodCallNode): ?Node
    {
        $this->identifierRenamer->renameNodeWithMap($methodCallNode, $this->renameMethodsMap);
        $this->moveFunctionArgumentsUp($methodCallNode);

        return $methodCallNode;
    }

    private function moveFunctionArgumentsUp(MethodCall $methodCallNode): void
    {
        /** @var FuncCall $isFunctionNode */
        $isFunctionNode = $methodCallNode->args[0]->value;

        $argument = $isFunctionNode->args[0];
        $isFunctionName = (string) $isFunctionNode->name;

        $oldArguments = $methodCallNode->args;
        unset($oldArguments[0]);

        $methodCallNode->args = array_merge([
            new Arg($this->nodeFactory->createString($this->oldMethodsToTypes[$isFunctionName])),
            $argument,
        ], $oldArguments);
    }

    private function isNamedFunction(Expr $node): bool
    {
        if (! $node instanceof FuncCall) {
            return false;
        }

        $functionName = $node->name;
        return $functionName instanceof Name;
    }
}
