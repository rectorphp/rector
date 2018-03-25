<?php declare(strict_types=1);

namespace Rector\Rector\Contrib\PHPUnit\SpecificMethod;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use Rector\Node\NodeFactory;
use Rector\NodeAnalyzer\MethodCallAnalyzer;
use Rector\NodeChanger\IdentifierRenamer;
use Rector\Rector\AbstractPHPUnitRector;

/**
 * Before:
 * - $this->assertTrue(property_exists(new Class, 'property'), 'message');
 * - $this->assertFalse(property_exists(new Class, 'property'), 'message');
 *
 * After:
 * - $this->assertClassHasAttribute('property', 'Class', 'message');
 * - $this->assertClassNotHasAttribute('property', 'Class', 'message');
 */
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

    public function __construct(
        MethodCallAnalyzer $methodCallAnalyzer,
        IdentifierRenamer $identifierRenamer,
        NodeFactory $nodeFactory
    ) {
        $this->methodCallAnalyzer = $methodCallAnalyzer;
        $this->identifierRenamer = $identifierRenamer;
        $this->nodeFactory = $nodeFactory;
    }

    public function isCandidate(Node $node): bool
    {
        if (! $this->isInTestClass($node)) {
            return false;
        }

        if (! $this->methodCallAnalyzer->isMethods($node, ['assertTrue', 'assertFalse'])) {
            return false;
        }

        /** @var MethodCall $methodCallAnalyzer */
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
     * @var MethodCall
     */
    public function refactor(Node $methodCallNode): ?Node
    {
        $this->identifierRenamer->renameNodeWithMap($methodCallNode, [
            'assertTrue' => 'assertClassHasAttribute',
            'assertFalse' => 'assertClassNotHasAttribute',
        ]);

        /** @var Identifier $oldArguments */
        $oldArguments = $methodCallNode->args;
        $propertyExistsArguments = $oldArguments[0]->value;
        [$firstArgument, $secondArgument] = $propertyExistsArguments->args;

        $className = $firstArgument->value->class->toString();
        $propertyName = $secondArgument->value->value;

        unset($oldArguments[0]);

        $methodCallNode->args = array_merge($this->nodeFactory->createArgs([
            $propertyName, $className,
        ]), $oldArguments);

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
