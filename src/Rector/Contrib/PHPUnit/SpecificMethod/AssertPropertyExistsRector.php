<?php declare(strict_types=1);

namespace Rector\Rector\Contrib\PHPUnit\SpecificMethod;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use Rector\Builder\IdentifierRenamer;
use Rector\Node\NodeFactory;
use Rector\NodeAnalyzer\MethodCallAnalyzer;
use Rector\Rector\AbstractPHPUnitRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;

final class AssertPropertyExistsRector extends AbstractPHPUnitRector
{
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

    public function isCandidate(Node $node): bool
    {
        if (! $this->isInTestClass($node)) {
            return false;
        }

        if (! $this->methodCallAnalyzer->isMethods($node, ['assertTrue', 'assertFalse'])) {
            return false;
        }

        /** @var MethodCall $methodCallNode */
        $methodCallNode = $node;

        /** @var FuncCall $firstArgumentValue */
        $firstArgumentValue = $methodCallNode->args[0]->value;
        if (! $this->isNamedFunction($firstArgumentValue)) {
            return false;
        }

        $methodName = $firstArgumentValue->name->toString();

        return $methodName === 'property_exists';
    }

    /**
     * @param MethodCall $methodCallNode
     */
    public function refactor(Node $methodCallNode): ?Node
    {
        /** @var Identifier $oldArguments */
        $oldArguments = $methodCallNode->args;
        $propertyExistsArguments = $oldArguments[0]->value;
        [$firstArgument, $secondArgument] = $propertyExistsArguments->args;

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

    private function isNamedFunction(Expr $node): bool
    {
        if (! $node instanceof FuncCall) {
            return false;
        }

        $functionName = $node->name;
        return $functionName instanceof Name;
    }
}
