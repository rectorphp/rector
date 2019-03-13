<?php declare(strict_types=1);

namespace Rector\PHPUnit\Rector\MethodCall;

use PhpParser\BuilderHelpers;
use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\ArrayDimFetch;
use PhpParser\Node\Expr\BinaryOp\Identical;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Name;
use Rector\Rector\AbstractPHPUnitRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;

/**
 * @see https://github.com/sebastianbergmann/phpunit/issues/3494
 * @see https://github.com/sebastianbergmann/phpunit/issues/3495
 */
final class ReplaceAssertArraySubsetRector extends AbstractPHPUnitRector
{
    /**
     * @var Expr[]
     */
    private $expectedKeys = [];

    /**
     * @var Expr[]
     */
    private $expectedValuesByKeys = [];

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Replace deprecated "assertArraySubset()" method with alternative methods', [
            new CodeSample(
                <<<'CODE_SAMPLE'
class SomeTest extends \PHPUnit\Framework\TestCase
{
    public function test()
    {
        $checkedArray = [];

        $this->assertArraySubset([
           'cache_directory' => 'new_value',
        ], $checkedArray);
    }
}
CODE_SAMPLE
                ,
                <<<'CODE_SAMPLE'
class SomeTest extends \PHPUnit\Framework\TestCase
{
    public function test()
    {
        $checkedArray = [];

        $this->assertArrayHasKey('cache_directory', $checkedArray);
        $this->assertSame('new_value', $checkedArray['cache_directory']);
    }
}
CODE_SAMPLE
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
        if (! $this->isAssertMethod($node, 'assertArraySubset')) {
            return null;
        }

        $this->reset();

        $expectedArray = $this->matchArray($node->args[0]->value);
        if ($expectedArray === null) {
            return null;
        }

        $this->collectExpectedKeysAndValues($expectedArray);

        if ($this->expectedKeys === []) {
            // no keys → intersect!
            $arrayIntersect = new FuncCall(new Name('array_intersect'));
            $arrayIntersect->args[] = new Arg($expectedArray);
            $arrayIntersect->args[] = $node->args[1];

            $identical = new Identical($arrayIntersect, $expectedArray);

            $assertTrue = $this->createCallWithName($node, 'assertTrue');
            $assertTrue->args[] = new Arg($identical);

            $this->addNodeAfterNode($assertTrue, $node);
        } else {
            $this->addKeyAsserts($node);
            $this->addValueAsserts($node);
        }

        $this->removeNode($node);

        return null;
    }

    /**
     * @param MethodCall|StaticCall $node
     */
    private function addKeyAsserts(Node $node): void
    {
        foreach ($this->expectedKeys as $expectedKey) {
            $assertArrayHasKey = $this->createCallWithName($node, 'assertArrayHasKey');
            $assertArrayHasKey->args[0] = new Arg($expectedKey);
            $assertArrayHasKey->args[1] = $node->args[1];

            $this->addNodeAfterNode($assertArrayHasKey, $node);
        }
    }

    /**
     * @param MethodCall|StaticCall $node
     */
    private function addValueAsserts(Node $node): void
    {
        foreach ($this->expectedValuesByKeys as $key => $expectedValue) {
            $assertSame = $this->createCallWithName($node, 'assertSame');
            $assertSame->args[0] = new Arg($expectedValue);

            $arrayDimFetch = new ArrayDimFetch($node->args[1]->value, BuilderHelpers::normalizeValue($key));
            $assertSame->args[1] = new Arg($arrayDimFetch);

            $this->addNodeAfterNode($assertSame, $node);
        }
    }

    /**
     * @param StaticCall|MethodCall $node
     * @return StaticCall|MethodCall
     */
    private function createCallWithName(Node $node, string $name): Node
    {
        return $node instanceof MethodCall ? new MethodCall($node->var, $name) : new StaticCall($node->class, $name);
    }

    private function collectExpectedKeysAndValues(Array_ $expectedArray): void
    {
        foreach ($expectedArray->items as $arrayItem) {
            if ($arrayItem->key === null) {
                continue;
            }

            $this->expectedKeys[] = $arrayItem->key;

            $key = $this->getValue($arrayItem->key);
            $this->expectedValuesByKeys[$key] = $arrayItem->value;
        }
    }

    private function reset(): void
    {
        $this->expectedKeys = [];
        $this->expectedValuesByKeys = [];
    }

    private function matchArray(Expr $expr): ?Array_
    {
        if ($expr instanceof Array_) {
            return $expr;
        }

        $value = $this->getValue($expr);

        // nothing we can do
        if ($value === null || ! is_array($value)) {
            return null;
        }

        // use specific array instead
        return BuilderHelpers::normalizeValue($value);
    }
}
