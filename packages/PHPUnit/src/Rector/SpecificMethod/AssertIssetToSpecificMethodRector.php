<?php declare(strict_types=1);

namespace Rector\PHPUnit\Rector\SpecificMethod;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\ArrayDimFetch;
use PhpParser\Node\Expr\Isset_;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Scalar\String_;
use Rector\PhpParser\Node\Manipulator\IdentifierManipulator;
use Rector\Rector\AbstractPHPUnitRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;

/**
 * @see \Rector\PHPUnit\Tests\Rector\SpecificMethod\AssertIssetToSpecificMethodRector\AssertIssetToSpecificMethodRectorTest
 */
final class AssertIssetToSpecificMethodRector extends AbstractPHPUnitRector
{
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
        return new RectorDefinition('Turns isset comparisons to their method name alternatives in PHPUnit TestCase', [
            new CodeSample(
                '$this->assertTrue(isset($anything->foo));',
                '$this->assertFalse(isset($anything["foo"]), "message");'
            ),
            new CodeSample(
                '$this->assertObjectHasAttribute("foo", $anything);',
                '$this->assertArrayNotHasKey("foo", $anything, "message");'
            ),
        ]);
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
        if (! $this->isPHPUnitMethodNames($node, ['assertTrue', 'assertFalse'])) {
            return null;
        }

        $firstArgumentValue = $node->args[0]->value;
        // is property access
        if (! $firstArgumentValue instanceof Isset_) {
            return null;
        }
        $variableNodeClass = get_class($firstArgumentValue->vars[0]);
        if (! in_array($variableNodeClass, [ArrayDimFetch::class, PropertyFetch::class], true)) {
            return null;
        }
        /** @var Isset_ $issetNode */
        $issetNode = $node->args[0]->value;

        $issetNodeArg = $issetNode->vars[0];

        if ($issetNodeArg instanceof PropertyFetch) {
            $this->refactorPropertyFetchNode($node, $issetNodeArg);
        } elseif ($issetNodeArg instanceof ArrayDimFetch) {
            $this->refactorArrayDimFetchNode($node, $issetNodeArg);
        }

        return $node;
    }

    /**
     * @param MethodCall|StaticCall $node
     * @param PropertyFetch $expr
     */
    private function refactorPropertyFetchNode(Node $node, Expr $expr): void
    {
        $name = $this->getName($expr);
        if ($name === null) {
            return;
        }

        $this->identifierManipulator->renameNodeWithMap($node, [
            'assertTrue' => 'assertObjectHasAttribute',
            'assertFalse' => 'assertObjectNotHasAttribute',
        ]);

        $oldArgs = $node->args;
        unset($oldArgs[0]);

        $node->args = array_merge($this->createArgs([new String_($name), $expr->var]), $oldArgs);
    }

    /**
     * @param MethodCall|StaticCall $node
     */
    private function refactorArrayDimFetchNode(Node $node, ArrayDimFetch $arrayDimFetch): void
    {
        $this->identifierManipulator->renameNodeWithMap($node, [
            'assertTrue' => 'assertArrayHasKey',
            'assertFalse' => 'assertArrayNotHasKey',
        ]);

        $oldArgs = $node->args;

        unset($oldArgs[0]);

        $node->args = array_merge($this->createArgs([$arrayDimFetch->dim, $arrayDimFetch->var]), $oldArgs);
    }
}
