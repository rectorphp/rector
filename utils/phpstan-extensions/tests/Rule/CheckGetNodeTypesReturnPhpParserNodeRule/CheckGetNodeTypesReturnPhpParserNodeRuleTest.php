<?php

declare(strict_types=1);

namespace Rector\PHPStanExtensions\Tests\Rule\CheckGetNodeTypesReturnPhpParserNodeRule;

use Iterator;
use PhpParser\Node;
use PHPStan\Rules\Rule;
use Rector\PHPStanExtensions\Rule\CheckGetNodeTypesReturnPhpParserNodeRule;
use Rector\PHPStanExtensions\Tests\Rule\CheckGetNodeTypesReturnPhpParserNodeRule\Fixture\IncorrectReturnRector;
use Rector\PHPStanExtensions\Tests\Rule\CheckGetNodeTypesReturnPhpParserNodeRule\Source\ClassNotOfPhpParserNode;
use Symplify\PHPStanExtensions\Testing\AbstractServiceAwareRuleTestCase;

final class CheckGetNodeTypesReturnPhpParserNodeRuleTest extends AbstractServiceAwareRuleTestCase
{
    /**
     * @dataProvider provideData()
     * @param array<string|string[]|int[]> $expectedErrorsWithLines
     */
    public function testRule(string $filePath, array $expectedErrorsWithLines): void
    {
        $this->analyse([$filePath], $expectedErrorsWithLines);
    }

    public function provideData(): Iterator
    {
        yield [__DIR__ . '/Fixture/SomeRector.php', []];
        yield [__DIR__ . '/Fixture/SkipNoGetNodeTypes.php', []];
        yield [__DIR__ . '/Fixture/SkipInterface.php', []];

        $errorMessage = sprintf(
            CheckGetNodeTypesReturnPhpParserNodeRule::ERROR_MESSAGE,
            IncorrectReturnRector::class,
            Node::class,
            ClassNotOfPhpParserNode::class
        );
        yield [__DIR__ . '/Fixture/IncorrectReturnRector.php', [[$errorMessage, 14]]];
    }

    protected function getRule(): Rule
    {
        return $this->getRuleFromConfig(
            CheckGetNodeTypesReturnPhpParserNodeRule::class,
            __DIR__ . '/config/configured_rule.neon'
        );
    }
}
