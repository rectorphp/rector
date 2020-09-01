<?php

declare(strict_types=1);

namespace Rector\PHPStanExtensions\Tests\Rule\CheckNotTestsNamespaceOutsideTestsDirectoryRule;

use Iterator;
use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;
use Rector\PHPStanExtensions\Rule\CheckNotTestsNamespaceOutsideTestsDirectoryRule;

final class CheckNotTestsNamespaceOutsideTestsDirectoryRuleTest extends RuleTestCase
{
    /**
     * @dataProvider provideData()
     */
    public function testRule(string $filePath, array $expectedErrorsWithLines): void
    {
        $this->analyse([$filePath], $expectedErrorsWithLines);
    }

    public function provideData(): Iterator
    {
        yield [__DIR__ . '/Fixture/tests/TestsNamespaceOutsideTestsDirectoryClass.php', []];
    }

    protected function getRule(): Rule
    {
        return new CheckNotTestsNamespaceOutsideTestsDirectoryRule();
    }
}
