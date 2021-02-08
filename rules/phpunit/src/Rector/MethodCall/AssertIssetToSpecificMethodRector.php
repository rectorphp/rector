<?php

declare(strict_types=1);

namespace Rector\PHPUnit\Rector\MethodCall;

use PhpParser\Node;
use PhpParser\Node\Expr\ArrayDimFetch;
use PhpParser\Node\Expr\Isset_;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Scalar\String_;
use Rector\Core\Rector\AbstractRector;
use Rector\PHPUnit\NodeAnalyzer\TestsNodeAnalyzer;
use Rector\Renaming\NodeManipulator\IdentifierManipulator;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @see \Rector\PHPUnit\Tests\Rector\MethodCall\AssertIssetToSpecificMethodRector\AssertIssetToSpecificMethodRectorTest
 */
final class AssertIssetToSpecificMethodRector extends AbstractRector
{
    /**
     * @var string
     */
    private const ASSERT_TRUE = 'assertTrue';

    /**
     * @var string
     */
    private const ASSERT_FALSE = 'assertFalse';

    /**
     * @var IdentifierManipulator
     */
    private $identifierManipulator;

    /**
     * @var TestsNodeAnalyzer
     */
    private $testsNodeAnalyzer;

    public function __construct(IdentifierManipulator $identifierManipulator, TestsNodeAnalyzer $testsNodeAnalyzer)
    {
        $this->identifierManipulator = $identifierManipulator;
        $this->testsNodeAnalyzer = $testsNodeAnalyzer;
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Turns isset comparisons to their method name alternatives in PHPUnit TestCase',
            [
                new CodeSample(
                    '$this->assertTrue(isset($anything->foo));',
                    '$this->assertObjectHasAttribute("foo", $anything);'
                ),
                new CodeSample(
                    '$this->assertFalse(isset($anything["foo"]), "message");',
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
        if (! $this->testsNodeAnalyzer->isPHPUnitMethodNames($node, [self::ASSERT_TRUE, self::ASSERT_FALSE])) {
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
     */
    private function refactorPropertyFetchNode(Node $node, PropertyFetch $propertyFetch): void
    {
        $name = $this->getName($propertyFetch);
        if ($name === null) {
            return;
        }

        $this->identifierManipulator->renameNodeWithMap($node, [
            self::ASSERT_TRUE => 'assertObjectHasAttribute',
            self::ASSERT_FALSE => 'assertObjectNotHasAttribute',
        ]);

        $oldArgs = $node->args;
        unset($oldArgs[0]);

        $newArgs = $this->nodeFactory->createArgs([new String_($name), $propertyFetch->var]);
        $node->args = $this->appendArgs($newArgs, $oldArgs);
    }

    /**
     * @param MethodCall|StaticCall $node
     */
    private function refactorArrayDimFetchNode(Node $node, ArrayDimFetch $arrayDimFetch): void
    {
        $this->identifierManipulator->renameNodeWithMap($node, [
            self::ASSERT_TRUE => 'assertArrayHasKey',
            self::ASSERT_FALSE => 'assertArrayNotHasKey',
        ]);

        $oldArgs = $node->args;

        unset($oldArgs[0]);

        $node->args = array_merge($this->nodeFactory->createArgs([$arrayDimFetch->dim, $arrayDimFetch->var]), $oldArgs);
    }
}
