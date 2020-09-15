<?php

declare(strict_types=1);

namespace Rector\PHPUnit\Rector\MethodCall;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use Rector\Core\Rector\AbstractPHPUnitRector;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;

/**
 * @see https://github.com/sebastianbergmann/phpunit/blob/master/ChangeLog-9.0.md
 * @see https://github.com/sebastianbergmann/phpunit/commit/1ba2e3e1bb091acda3139f8a9259fa8161f3242d
 *
 * @see \Rector\PHPUnit\Tests\Rector\MethodCall\ExplicitPhpErrorApiRector\ExplicitPhpErrorApiRectorTest
 */
final class ExplicitPhpErrorApiRector extends AbstractPHPUnitRector
{
    /**
     * @var array<string, string>
     */
    private const REPLACEMENTS = [
        'PHPUnit\Framework\TestCase\Notice' => 'expectNotice',
        'PHPUnit\Framework\TestCase\Deprecated' => 'expectDeprecation',
        'PHPUnit\Framework\TestCase\Error' => 'expectError',
        'PHPUnit\Framework\TestCase\Warning' => 'expectWarning',
    ];

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition(
            'Use explicit API for expecting PHP errors, warnings, and notices',
            [
                new CodeSample(
                    <<<'CODE_SAMPLE'
final class SomeTest extends \PHPUnit\Framework\TestCase
{
    public function test()
    {
        $this->expectException(\PHPUnit\Framework\TestCase\Deprecated::class);
        $this->expectException(\PHPUnit\Framework\TestCase\Error::class);
        $this->expectException(\PHPUnit\Framework\TestCase\Notice::class);
        $this->expectException(\PHPUnit\Framework\TestCase\Warning::class);
    }
}
CODE_SAMPLE
                    ,
                    <<<'CODE_SAMPLE'
final class SomeTest extends \PHPUnit\Framework\TestCase
{
    public function test()
    {
        $this->expectDeprecation();
        $this->expectError();
        $this->expectNotice();
        $this->expectWarning();
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
        if (! $this->isPHPUnitMethodNames($node, ['expectException'])) {
            return null;
        }

        foreach (self::REPLACEMENTS as $class => $method) {
            $newNode = $this->replaceExceptionWith($node, $class, $method);
            if ($newNode !== null) {
                return $newNode;
            }
        }

        return $node;
    }

    /**
     * @param MethodCall|StaticCall $node
     */
    private function replaceExceptionWith(Node $node, string $exceptionClass, string $explicitMethod): ?Node
    {
        if (! isset($node->args[0])) {
            return null;
        }

        if (! $this->isClassConstReference($node->args[0]->value, $exceptionClass)) {
            return null;
        }

        return $this->createPHPUnitCallWithName($node, $explicitMethod);
    }
}
