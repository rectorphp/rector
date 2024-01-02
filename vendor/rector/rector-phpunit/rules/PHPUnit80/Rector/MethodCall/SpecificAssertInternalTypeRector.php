<?php

declare (strict_types=1);
namespace Rector\PHPUnit\PHPUnit80\Rector\MethodCall;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Identifier;
use PhpParser\Node\Scalar\String_;
use Rector\PHPUnit\NodeAnalyzer\TestsNodeAnalyzer;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://github.com/sebastianbergmann/phpunit/blob/master/ChangeLog-8.0.md
 * @changelog https://github.com/sebastianbergmann/phpunit/commit/a406c85c51edd76ace29119179d8c21f590c939e
 *
 * @see \Rector\PHPUnit\Tests\PHPUnit80\Rector\MethodCall\SpecificAssertInternalTypeRector\SpecificAssertInternalTypeRectorTest
 */
final class SpecificAssertInternalTypeRector extends AbstractRector
{
    /**
     * @readonly
     * @var \Rector\PHPUnit\NodeAnalyzer\TestsNodeAnalyzer
     */
    private $testsNodeAnalyzer;
    /**
     * @var array<string, string[]>
     */
    private const TYPE_TO_METHOD = ['array' => ['assertIsArray', 'assertIsNotArray'], 'bool' => ['assertIsBool', 'assertIsNotBool'], 'boolean' => ['assertIsBool', 'assertIsNotBool'], 'float' => ['assertIsFloat', 'assertIsNotFloat'], 'int' => ['assertIsInt', 'assertIsNotInt'], 'integer' => ['assertIsInt', 'assertIsNotInt'], 'numeric' => ['assertIsNumeric', 'assertIsNotNumeric'], 'object' => ['assertIsObject', 'assertIsNotObject'], 'resource' => ['assertIsResource', 'assertIsNotResource'], 'string' => ['assertIsString', 'assertIsNotString'], 'scalar' => ['assertIsScalar', 'assertIsNotScalar'], 'callable' => ['assertIsCallable', 'assertIsNotCallable'], 'iterable' => ['assertIsIterable', 'assertIsNotIterable'], 'null' => ['assertNull', 'assertNotNull']];
    public function __construct(TestsNodeAnalyzer $testsNodeAnalyzer)
    {
        $this->testsNodeAnalyzer = $testsNodeAnalyzer;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Change assertInternalType()/assertNotInternalType() method to new specific alternatives', [new CodeSample(<<<'CODE_SAMPLE'
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
, <<<'CODE_SAMPLE'
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
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [MethodCall::class, StaticCall::class];
    }
    /**
     * @param MethodCall|StaticCall $node
     */
    public function refactor(Node $node) : ?Node
    {
        if (!$this->testsNodeAnalyzer->isPHPUnitMethodCallNames($node, ['assertInternalType', 'assertNotInternalType'])) {
            return null;
        }
        if ($node->isFirstClassCallable()) {
            return null;
        }
        $typeNode = $node->getArgs()[0]->value;
        if (!$typeNode instanceof String_) {
            return null;
        }
        $type = $typeNode->value;
        if (!isset(self::TYPE_TO_METHOD[$type])) {
            return null;
        }
        \array_shift($node->args);
        $position = $this->isName($node->name, 'assertInternalType') ? 0 : 1;
        $methodName = self::TYPE_TO_METHOD[$type][$position];
        $node->name = new Identifier($methodName);
        return $node;
    }
}
