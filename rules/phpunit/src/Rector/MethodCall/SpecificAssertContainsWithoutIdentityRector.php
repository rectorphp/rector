<?php

declare(strict_types=1);

namespace Rector\PHPUnit\Rector\MethodCall;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Identifier;
use PHPStan\Type\StringType;
use Rector\Core\Rector\AbstractPHPUnitRector;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;

/**
 * @see https://github.com/sebastianbergmann/phpunit/issues/3426
 * @see \Rector\PHPUnit\Tests\Rector\MethodCall\SpecificAssertContainsWithoutIdentityRector\SpecificAssertContainsWithoutIdentityRectorTest
 */
final class SpecificAssertContainsWithoutIdentityRector extends AbstractPHPUnitRector
{
    /**
     * @var array<string, array<string, string>>
     */
    private const OLD_METHODS_NAMES_TO_NEW_NAMES = [
        'string' => [
            'assertContains' => 'assertContainsEquals',
            'assertNotContains' => 'assertNotContainsEquals',
        ],
    ];

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition(
            'Change assertContains()/assertNotContains() with non-strict comparison to new specific alternatives',
            [
                new CodeSample(
                    <<<'CODE_SAMPLE'
<?php

final class SomeTest extends \PHPUnit\Framework\TestCase
{
    public function test()
    {
        $objects = [ new \stdClass(), new \stdClass(), new \stdClass() ];
        $this->assertContains(new \stdClass(), $objects, 'message', false, false);
        $this->assertNotContains(new \stdClass(), $objects, 'message', false, false);
    }
}
CODE_SAMPLE
                    ,
                    <<<'CODE_SAMPLE'
<?php

final class SomeTest extends TestCase
{
    public function test()
    {
        $objects = [ new \stdClass(), new \stdClass(), new \stdClass() ];
        $this->assertContainsEquals(new \stdClass(), $objects, 'message');
        $this->assertNotContainsEquals(new \stdClass(), $objects, 'message');
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
        if (! $this->isPHPUnitMethodNames($node, ['assertContains', 'assertNotContains'])) {
            return null;
        }

        //when second argument is string: do nothing
        if ($this->isStaticType($node->args[1]->value, StringType::class)) {
            return null;
        }

        //when less then 5 arguments given: do nothing
        if (! isset($node->args[4]) || $node->args[4]->value === null) {
            return null;
        }

        //when 5th argument check identity is true: do nothing
        if ($this->isValue($node->args[4]->value, true)) {
            return null;
        }

        /* here we search for element of array without identity check  and we can replace functions */
        $methodName = $this->getName($node->name);

        $node->name = new Identifier(self::OLD_METHODS_NAMES_TO_NEW_NAMES['string'][$methodName]);
        unset($node->args[3], $node->args[4]);

        return $node;
    }
}
