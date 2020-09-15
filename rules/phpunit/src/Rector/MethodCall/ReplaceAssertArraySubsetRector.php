<?php

declare(strict_types=1);

namespace Rector\PHPUnit\Rector\MethodCall;

use PhpParser\BuilderHelpers;
use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\ArrayDimFetch;
use PhpParser\Node\Expr\ArrayItem;
use PhpParser\Node\Expr\BinaryOp\Identical;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Name;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\Core\Rector\AbstractPHPUnitRector;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;

/**
 * @see https://github.com/sebastianbergmann/phpunit/issues/3494
 * @see https://github.com/sebastianbergmann/phpunit/issues/3495
 *
 * @see \Rector\PHPUnit\Tests\Rector\MethodCall\ReplaceAssertArraySubsetRector\ReplaceAssertArraySubsetRectorTest
 */
final class ReplaceAssertArraySubsetRector extends AbstractPHPUnitRector
{
    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Replace deprecated "assertArraySubset()" method with alternative methods', [
            new CodeSample(
                <<<'CODE_SAMPLE'
use PHPUnit\Framework\TestCase;

final class SomeTest extends TestCase
{
    public function test()
    {
        $checkedArray = [];

        $this->assertArraySubset([
           'cache_directory' => 'new_value',
        ], $checkedArray, true);
    }
}
CODE_SAMPLE
                ,
                <<<'CODE_SAMPLE'
use PHPUnit\Framework\TestCase;

final class SomeTest extends TestCase
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
        if (! $this->isPHPUnitMethodName($node, 'assertArraySubset')) {
            return null;
        }

        $expectedArray = $this->matchArray($node->args[0]->value);
        if ($expectedArray === null) {
            return null;
        }

        $expectedArrayItems = $this->collectExpectedKeysAndValues($expectedArray);
        if ($expectedArrayItems === []) {
            // no keys → intersect!
            $funcCall = new FuncCall(new Name('array_intersect'));
            $funcCall->args[] = new Arg($expectedArray);
            $funcCall->args[] = $node->args[1];

            $identical = new Identical($funcCall, $expectedArray);

            $assertTrue = $this->createPHPUnitCallWithName($node, 'assertTrue');
            $assertTrue->args[] = new Arg($identical);

            $this->addNodeAfterNode($assertTrue, $node);
        } else {
            $this->addKeyAsserts($node, $expectedArrayItems);
            $this->addValueAsserts($node, $expectedArrayItems);
        }

        $this->removeNode($node);

        return null;
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

    /**
     * @return ArrayItem[]
     */
    private function collectExpectedKeysAndValues(Array_ $expectedArray): array
    {
        $expectedArrayItems = [];

        foreach ($expectedArray->items as $arrayItem) {
            if ($arrayItem === null) {
                continue;
            }

            if ($arrayItem->key === null) {
                continue;
            }

            $expectedArrayItems[] = $arrayItem;
        }

        return $expectedArrayItems;
    }

    /**
     * @param MethodCall|StaticCall $node
     * @param ArrayItem[] $expectedArrayItems
     */
    private function addKeyAsserts(Node $node, array $expectedArrayItems): void
    {
        foreach ($expectedArrayItems as $expectedArrayItem) {
            $assertArrayHasKey = $this->createPHPUnitCallWithName($node, 'assertArrayHasKey');

            if ($expectedArrayItem->key === null) {
                throw new ShouldNotHappenException();
            }

            $assertArrayHasKey->args[0] = new Arg($expectedArrayItem->key);
            $assertArrayHasKey->args[1] = $node->args[1];

            $this->addNodeAfterNode($assertArrayHasKey, $node);
        }
    }

    /**
     * @param MethodCall|StaticCall $node
     * @param ArrayItem[] $expectedArrayItems
     */
    private function addValueAsserts(Node $node, array $expectedArrayItems): void
    {
        $assertMethodName = $this->resolveAssertMethodName($node);

        foreach ($expectedArrayItems as $expectedArrayItem) {
            $assertSame = $this->createPHPUnitCallWithName($node, $assertMethodName);
            $assertSame->args[0] = new Arg($expectedArrayItem->value);

            $arrayDimFetch = new ArrayDimFetch($node->args[1]->value, BuilderHelpers::normalizeValue(
                $expectedArrayItem->key
            ));
            $assertSame->args[1] = new Arg($arrayDimFetch);

            $this->addNodeAfterNode($assertSame, $node);
        }
    }

    /**
     * @param MethodCall|StaticCall $node
     */
    private function resolveAssertMethodName(Node $node): string
    {
        if (! isset($node->args[2])) {
            return 'assertEquals';
        }

        $isStrict = $this->getValue($node->args[2]->value);

        return $isStrict ? 'assertSame' : 'assertEquals';
    }
}
