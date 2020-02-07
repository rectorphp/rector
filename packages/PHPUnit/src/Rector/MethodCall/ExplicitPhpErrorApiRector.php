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
 * @see \Rector\PHPUnit\Tests\Rector\MethodCall\ExplicitPhpErrorApiRector\ExplicitPhpErrorApiRectorTest
 */
final class ExplicitPhpErrorApiRector extends AbstractPHPUnitRector
{
    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition(
            'Use explicit API for expecting PHP errors, warnings, and notices',
            [
                new CodeSample(
                    <<<'PHP'
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
PHP
                    ,
                    <<<'PHP'
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
        if (! $this->isPHPUnitMethodNames($node, ['expectException'])) {
            return null;
        }

        $replacements = [
            'PHPUnit\Framework\TestCase\Notice' => 'expectNotice',
            'PHPUnit\Framework\TestCase\Deprecated' => 'expectDeprecation',
            'PHPUnit\Framework\TestCase\Error' => 'expectError',
            'PHPUnit\Framework\TestCase\Warning' => 'expectWarning',
        ];

        foreach ($replacements as $class => $method) {
            $this->replaceExceptionWith($node, $class, $method);
        }

        return $node;
    }

    /**
     * @param MethodCall|StaticCall $node
     * @param string $exceptionClass
     * @param string $explicitMethod
     */
    private function replaceExceptionWith(Node $node, $exceptionClass, $explicitMethod): void
    {
        if (isset($node->args[0]) && property_exists(
            $node->args[0]->value,
            'class'
        ) && (string) $node->args[0]->value->class === $exceptionClass) {
            $this->replaceNode($node, $this->createPHPUnitCallWithName($node, $explicitMethod));
        }
    }
}
