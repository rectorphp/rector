<?php declare(strict_types=1);

namespace Rector\PHPUnit\Rector\SpecificMethod;

use PhpParser\Node;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Identifier;
use Rector\Builder\IdentifierRenamer;
use Rector\Node\NodeFactory;
use Rector\Rector\AbstractPHPUnitRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;

final class AssertPropertyExistsRector extends AbstractPHPUnitRector
{
    /**
     * @var IdentifierRenamer
     */
    private $identifierRenamer;

    /**
     * @var NodeFactory
     */
    private $nodeFactory;

    /**
     * @var string[]
     */
    private $renameMethodsWithObjectMap = [
        'assertTrue' => 'assertObjectHasAttribute',
        'assertFalse' => 'assertObjectNotHasAttribute',
    ];

    /**
     * @var string[]
     */
    private $renameMethodsWithClassMap = [
        'assertTrue' => 'assertClassHasAttribute',
        'assertFalse' => 'assertClassNotHasAttribute',
    ];

    public function __construct(IdentifierRenamer $identifierRenamer, NodeFactory $nodeFactory)
    {
        $this->identifierRenamer = $identifierRenamer;
        $this->nodeFactory = $nodeFactory;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition(
            'Turns `property_exists` comparisons to their method name alternatives in PHPUnit TestCase',
            [
                new CodeSample(
                    '$this->assertTrue(property_exists(new Class, "property"), "message");',
                    '$this->assertClassHasAttribute("property", "Class", "message");'
                ),
                new CodeSample(
                    '$this->assertFalse(property_exists(new Class, "property"), "message");',
                    '$this->assertClassNotHasAttribute("property", "Class", "message");'
                ),
            ]
        );
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [MethodCall::class];
    }

    /**
     * @param MethodCall $methodCallNode
     */
    public function refactor(Node $methodCallNode): ?Node
    {
        if (! $methodCallNode instanceof MethodCall) {
            return null;
        }

        if (! $this->isInTestClass($methodCallNode)) {
            return null;
        }

        if (! $this->isNames($methodCallNode, ['assertTrue', 'assertFalse'])) {
            return null;
        }

        /** @var FuncCall $firstArgumentValue */
        $firstArgumentValue = $methodCallNode->args[0]->value;

        if (! $this->isName($firstArgumentValue, 'property_exists')) {
            return null;
        }

        $oldArguments = $methodCallNode->args;

        /** @var Identifier $oldArguments */
        $propertyExistsMethodCall = $oldArguments[0]->value;

        /** @var MethodCall $propertyExistsMethodCall */
        [$firstArgument, $secondArgument] = $propertyExistsMethodCall->args;

        if ($firstArgument->value instanceof Variable) {
            $secondArg = new Variable($firstArgument->value->name);
            $map = $this->renameMethodsWithObjectMap;
        } else {
            $secondArg = $firstArgument->value->class->toString();
            $map = $this->renameMethodsWithClassMap;
        }

        unset($oldArguments[0]);

        $methodCallNode->args = array_merge($this->nodeFactory->createArgs([
            $secondArgument->value->value,
            $secondArg,
        ]), $oldArguments);

        $this->identifierRenamer->renameNodeWithMap($methodCallNode, $map);

        return $methodCallNode;
    }
}
