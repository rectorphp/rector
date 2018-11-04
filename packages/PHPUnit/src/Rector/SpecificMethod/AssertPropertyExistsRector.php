<?php declare(strict_types=1);

namespace Rector\PHPUnit\Rector\SpecificMethod;

use PhpParser\Node;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Identifier;
use PhpParser\Node\Scalar\String_;
use Rector\PhpParser\Node\Builder\IdentifierRenamer;
use Rector\Rector\AbstractPHPUnitRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;

final class AssertPropertyExistsRector extends AbstractPHPUnitRector
{
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

    /**
     * @var IdentifierRenamer
     */
    private $identifierRenamer;

    public function __construct(IdentifierRenamer $identifierRenamer)
    {
        $this->identifierRenamer = $identifierRenamer;
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

        /** @var FuncCall $firstArgumentValue */
        $firstArgumentValue = $node->args[0]->value;

        if (! $this->isName($firstArgumentValue, 'property_exists')) {
            return null;
        }

        /** @var Identifier $oldArguments */
        $propertyExistsMethodCall = $node->args[0]->value;
        if (! $propertyExistsMethodCall instanceof FuncCall) {
            return null;
        }

        [$firstArgument, $secondArgument] = $propertyExistsMethodCall->args;

        if ($firstArgument->value instanceof Variable) {
            $secondArg = new Variable($firstArgument->value->name);
            $map = $this->renameMethodsWithObjectMap;
        } elseif ($firstArgument->value instanceof New_) {
            $secondArg = $this->getName($firstArgument->value->class);
            $map = $this->renameMethodsWithClassMap;
        } else {
            return null;
        }

        if (! $secondArgument->value instanceof String_) {
            return null;
        }

        unset($node->args[0]);

        $node->args = array_merge($this->createArgs([$secondArgument->value->value, $secondArg]), $node->args);

        $this->identifierRenamer->renameNodeWithMap($node, $map);

        return $node;
    }
}
