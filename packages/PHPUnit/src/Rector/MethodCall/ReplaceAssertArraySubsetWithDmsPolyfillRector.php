<?php

declare(strict_types=1);

namespace Rector\PHPUnit\Rector\MethodCall;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use Rector\Rector\AbstractPHPUnitRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;

/**
 * @see https://github.com/sebastianbergmann/phpunit/issues/3494#issuecomment-480283612
 */

/**
 * @see \Rector\PHPUnit\Tests\Rector\MethodCall\ReplaceAssertArraySubsetWithDmsPolyfillRector\ReplaceAssertArraySubsetWithDmsPolyfillRectorTest
 */
final class ReplaceAssertArraySubsetWithDmsPolyfillRector extends AbstractPHPUnitRector
{
    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition(
            'Change assertArraySubset() to static call of DMS\PHPUnitExtensions\ArraySubset\Assert',
            [
                new CodeSample(
                    <<<'PHP'
use PHPUnit\Framework\TestCase;

class SomeClass extends TestCase
{
    public function test()
    {
        self::assertArraySubset(['bar' => 0], ['bar' => '0'], true);

        $this->assertArraySubset(['bar' => 0], ['bar' => '0'], true);
    }
}
PHP
,
                    <<<'PHP'
use PHPUnit\Framework\TestCase;

class SomeClass extends TestCase
{
    public function test()
    {
        \DMS\PHPUnitExtensions\ArraySubset\Assert::assertArraySubset(['bar' => 0], ['bar' => '0'], true);

        \DMS\PHPUnitExtensions\ArraySubset\Assert::assertArraySubset(['bar' => 0], ['bar' => '0'], true);
    }
}
PHP

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
        if (! $this->isPHPUnitMethodName($node, 'assertArraySubset')) {
            return null;
        }

        return $this->createStaticCall('DMS\PHPUnitExtensions\ArraySubset\Assert', 'assertArraySubset',
            $node->args
        );
    }
}
