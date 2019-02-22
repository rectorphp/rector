<?php declare(strict_types=1);

namespace Rector\PHPUnit\Rector\SpecificMethod;

use PhpParser\Node;
use PhpParser\Node\Expr\ArrayDimFetch;
use PhpParser\Node\Expr\Isset_;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Scalar\String_;
use Rector\PhpParser\Node\Maintainer\IdentifierMaintainer;
use Rector\Rector\AbstractPHPUnitRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;

final class AssertIssetToSpecificMethodRector extends AbstractPHPUnitRector
{
    /**
     * @var IdentifierMaintainer
     */
    private $identifierMaintainer;

    public function __construct(IdentifierMaintainer $IdentifierMaintainer)
    {
        $this->identifierMaintainer = $IdentifierMaintainer;
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
        return [MethodCall::class];
    }

    /**
     * @param MethodCall $node
     */
    public function refactor(Node $node): ?Node
    {
        if (! $this->isInTestClass($node)) {
            return null;
        }

        if (! $this->isNames($node, ['assertTrue', 'assertFalse'])) {
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

    private function refactorPropertyFetchNode(MethodCall $methodCall, PropertyFetch $propertyFetch): void
    {
        $name = $this->getName($propertyFetch);
        if ($name === null) {
            return;
        }

        $this->identifierMaintainer->renameNodeWithMap($methodCall, [
            'assertTrue' => 'assertObjectHasAttribute',
            'assertFalse' => 'assertObjectNotHasAttribute',
        ]);

        $oldArgs = $methodCall->args;
        unset($oldArgs[0]);

        $methodCall->args = array_merge($this->createArgs([new String_($name), $propertyFetch->var]), $oldArgs);
    }

    private function refactorArrayDimFetchNode(MethodCall $methodCall, ArrayDimFetch $arrayDimFetch): void
    {
        $this->identifierMaintainer->renameNodeWithMap($methodCall, [
            'assertTrue' => 'assertArrayHasKey',
            'assertFalse' => 'assertArrayNotHasKey',
        ]);

        $oldArgs = $methodCall->args;

        unset($oldArgs[0]);

        $methodCall->args = array_merge($this->createArgs([$arrayDimFetch->dim, $arrayDimFetch->var]), $oldArgs);
    }
}
