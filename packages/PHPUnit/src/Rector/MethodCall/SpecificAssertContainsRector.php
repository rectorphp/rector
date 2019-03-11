<?php declare(strict_types=1);

namespace Rector\PHPUnit\Rector\MethodCall;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Identifier;
use Rector\Rector\AbstractPHPUnitRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;

/**
 * @see https://github.com/sebastianbergmann/phpunit/blob/master/ChangeLog-8.0.md
 */
final class SpecificAssertContainsRector extends AbstractPHPUnitRector
{
    /**
     * @var string[][]
     */
    private $oldMethodsNamesToNewNames = [
        'string' => [
            'assertContains' => 'assertStringContainsString',
            'assertNotContains' => 'assertStringNotContainsString',
        ],
    ];

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition(
            'Change assertContains()/assertNotContains() method to new string and iterable alternatives',
            [
                new CodeSample(
                    <<<'CODE_SAMPLE'
<?php

final class SomeTest extends \PHPUnit\Framework\TestCase
{
    public function test()
    {
        $this->assertContains('foo', 'foo bar');
        $this->assertNotContains('foo', 'foo bar');
    }
}
CODE_SAMPLE
                    ,
                    <<<'CODE_SAMPLE'
<?php

final class SomeTest extends \PHPUnit\Framework\TestCase
{
    public function test()
    {
        $this->assertStringContains('foo', 'foo bar');
        $this->assertStringNotContains('foo', 'foo bar');
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
     * @param MethodCall $node
     */
    public function refactor(Node $node): ?Node
    {
        if (! $this->isInTestClass($node)) {
            return null;
        }

        if (! $this->isNames($node, ['assertContains', 'assertNotContains'])) {
            return null;
        }

        if (! $this->isStringType($node->args[1]->value)) {
            return null;
        }

        $node->name = new Identifier($this->oldMethodsNamesToNewNames['string'][$this->getName($node)]);

        return $node;
    }
}
