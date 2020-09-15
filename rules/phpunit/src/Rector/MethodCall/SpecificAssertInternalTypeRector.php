<?php

declare(strict_types=1);

namespace Rector\PHPUnit\Rector\MethodCall;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Identifier;
use PhpParser\Node\Scalar\String_;
use Rector\Core\Php\TypeAnalyzer;
use Rector\Core\Rector\AbstractPHPUnitRector;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;

/**
 * @see https://github.com/sebastianbergmann/phpunit/blob/master/ChangeLog-8.0.md
 * @see https://github.com/sebastianbergmann/phpunit/commit/a406c85c51edd76ace29119179d8c21f590c939e
 * @see \Rector\PHPUnit\Tests\Rector\MethodCall\SpecificAssertInternalTypeRector\SpecificAssertInternalTypeRectorTest
 */
final class SpecificAssertInternalTypeRector extends AbstractPHPUnitRector
{
    /**
     * @var string[][]
     */
    private const TYPE_TO_METHOD = [
        'array' => ['assertIsArray', 'assertIsNotArray'],
        'bool' => ['assertIsBool', 'assertIsNotBool'],
        'float' => ['assertIsFloat', 'assertIsNotFloat'],
        'int' => ['assertIsInt', 'assertIsNotInt'],
        'numeric' => ['assertIsNumeric', 'assertIsNotNumeric'],
        'object' => ['assertIsObject', 'assertIsNotObject'],
        'resource' => ['assertIsResource', 'assertIsNotResource'],
        'string' => ['assertIsString', 'assertIsNotString'],
        'scalar' => ['assertIsScalar', 'assertIsNotScalar'],
        'callable' => ['assertIsCallable', 'assertIsNotCallable'],
        'iterable' => ['assertIsIterable', 'assertIsNotIterable'],
        'null' => ['assertNull', 'assertNotNull'],
    ];

    /**
     * @var TypeAnalyzer
     */
    private $typeAnalyzer;

    public function __construct(TypeAnalyzer $typeAnalyzer)
    {
        $this->typeAnalyzer = $typeAnalyzer;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition(
            'Change assertInternalType()/assertNotInternalType() method to new specific alternatives',
            [
                new CodeSample(
                    <<<'CODE_SAMPLE'
final class SomeTest extends \PHPUnit\Framework\TestCase
{
    public function test()
    {
        $value = 'value';
        $this->assertInternalType('string', $value);
        $this->assertNotInternalType('array', $value);
    }
}
CODE_SAMPLE
                    ,
                    <<<'CODE_SAMPLE'
final class SomeTest extends \PHPUnit\Framework\TestCase
{
    public function test()
    {
        $value = 'value';
        $this->assertIsString($value);
        $this->assertIsNotArray($value);
    }
}
CODE_SAMPLE
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
        if (! $this->isPHPUnitMethodNames($node, ['assertInternalType', 'assertNotInternalType'])) {
            return null;
        }

        $typeNode = $node->args[0]->value;
        if (! $typeNode instanceof String_) {
            return null;
        }

        $type = $this->typeAnalyzer->normalizeType($typeNode->value);
        if (! isset(self::TYPE_TO_METHOD[$type])) {
            return null;
        }

        array_shift($node->args);

        $position = $this->isName($node->name, 'assertInternalType') ? 0 : 1;
        $methodName = self::TYPE_TO_METHOD[$type][$position];

        $node->name = new Identifier($methodName);

        return $node;
    }
}
