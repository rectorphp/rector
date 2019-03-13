<?php declare(strict_types=1);

namespace Rector\PHPUnit\Rector\SpecificMethod;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Scalar\String_;
use Rector\PhpParser\Node\Manipulator\IdentifierManipulator;
use Rector\Rector\AbstractPHPUnitRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;

final class AssertTrueFalseInternalTypeToSpecificMethodRector extends AbstractPHPUnitRector
{
    /**
     * @var string[]
     */
    private $oldFunctionsToTypes = [
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
     * @var IdentifierManipulator
     */
    private $identifierManipulator;

    public function __construct(IdentifierManipulator $identifierManipulator)
    {
        $this->identifierManipulator = $identifierManipulator;
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

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [MethodCall::class, StaticCall::class];
    }

    /**
     * @param MethodCall|StaticCall $node
     */
    public function refactor(Node $node): ?Node
    {
        if (! $this->isPHPUnitMethodNames($node, array_keys($this->renameMethodsMap))) {
            return null;
        }

        /** @var FuncCall $firstArgumentValue */
        $firstArgumentValue = $node->args[0]->value;

        $functionName = $this->getName($firstArgumentValue);
        if (! isset($this->oldFunctionsToTypes[$functionName])) {
            return null;
        }
        $this->identifierManipulator->renameNodeWithMap($node, $this->renameMethodsMap);
        $this->moveFunctionArgumentsUp($node);

        return $node;
    }

    /**
     * @param MethodCall|StaticCall $node
     */
    private function moveFunctionArgumentsUp(Node $node): void
    {
        /** @var FuncCall $isFunctionNode */
        $isFunctionNode = $node->args[0]->value;

        $argument = $isFunctionNode->args[0];
        $isFunctionName = $this->getName($isFunctionNode);

        $oldArguments = $node->args;
        unset($oldArguments[0]);

        $node->args = array_merge([
            new Arg(new String_($this->oldFunctionsToTypes[$isFunctionName])),
            $argument,
        ], $oldArguments);
    }
}
