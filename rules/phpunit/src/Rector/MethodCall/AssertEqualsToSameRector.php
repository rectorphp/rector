<?php

declare(strict_types=1);

namespace Rector\PHPUnit\Rector\MethodCall;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use PHPStan\Analyser\Scope;
use PHPStan\Type\FloatType;
use PHPStan\Type\IntegerType;
use PHPStan\Type\StringType;
use PHPStan\Type\Type;
use Rector\Core\PhpParser\Node\Manipulator\IdentifierManipulator;
use Rector\Core\Rector\AbstractPHPUnitRector;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;
use Rector\NodeTypeResolver\Node\AttributeKey;

/**
 * @see \Rector\PHPUnit\Tests\Rector\MethodCall\AssertEqualsToSameRector\AssertEqualsToSameRectorTest
 */
final class AssertEqualsToSameRector extends AbstractPHPUnitRector
{
    /**
     * @var array<string, string>
     */
    private const RENAME_METHODS_MAP = [
        'assertEquals' => 'assertSame',
    ];

    /**
     * We exclude
     * - bool because this is taken care of AssertEqualsParameterToSpecificMethodsTypeRector
     * - null because this is taken care of AssertEqualsParameterToSpecificMethodsTypeRector
     *
     * @var string[]
     */
    private const SCALAR_TYPES = [FloatType::class, IntegerType::class, StringType::class];

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
            'Turns `assertEquals()` into stricter `assertSame()` for scalar values in PHPUnit TestCase',
            [
                new CodeSample(
                    '$this->assertEquals(2, $result, "message");',
                    '$this->assertSame(2, $result, "message");'
                ),
                new CodeSample(
                    '$this->assertEquals($aString, $result, "message");',
                    '$this->assertSame($aString, $result, "message");'
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
        if (! $this->isInTestClass($node)) {
            return null;
        }

        $methodNames = array_keys(self::RENAME_METHODS_MAP);
        if (! $this->isNames($node->name, $methodNames)) {
            return null;
        }

        if (! isset($node->args[0])) {
            return null;
        }

        $valueNode = $node->args[0];
        $valueNodeType = $this->getNodeType($valueNode->value);
        if (! $this->isTypes($valueNodeType, self::SCALAR_TYPES)) {
            return null;
        }

        $this->identifierManipulator->renameNodeWithMap($node, self::RENAME_METHODS_MAP);

        return $node;
    }

    private function getNodeType(Expr $expr): Type
    {
        /** @var Scope $nodeScope */
        $nodeScope = $expr->getAttribute(AttributeKey::SCOPE);

        return $nodeScope->getType($expr);
    }

    /**
     * @param string[] $types
     */
    private function isTypes(Type $valueType, array $types): bool
    {
        foreach ($types as $type) {
            if (is_a($valueType, $type, true)) {
                return true;
            }
        }

        return false;
    }
}
