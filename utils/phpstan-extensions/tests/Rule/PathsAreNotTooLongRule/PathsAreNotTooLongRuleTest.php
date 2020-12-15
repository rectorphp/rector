<?php

declare(strict_types=1);

namespace Rector\PHPStanExtensions\Tests\Rule\PathsAreNotTooLongRule;

use Iterator;
use PHPStan\Rules\Rule;
use Rector\PHPStanExtensions\Rule\PathsAreNotTooLongRule;
use Symplify\PHPStanExtensions\Testing\AbstractServiceAwareRuleTestCase;

final class PathsAreNotTooLongRuleTest extends AbstractServiceAwareRuleTestCase
{
    /**
     * @dataProvider provideData()
     * @param array<string|string[]> $expectedError
     */
    public function testRule(string $filePath, array $expectedError): void
    {
        $this->analyse([$filePath], $expectedError);
    }

    public function provideData(): Iterator
    {
        $fileName = 'PrettyLongClassNameWaaaaayyyTooLongFileNameWithLoooootsOfCharacters.php';
        $errorMessage = sprintf(PathsAreNotTooLongRule::ERROR_MESSAGE, $fileName);
        yield [__DIR__ . '/Fixture/Rector/'. fileName, [[$errorMessage]]];
    }

    protected function getRule(): Rule
    {
        return $this->getRuleFromConfig(
            PathsAreNotTooLongRule::class,
            __DIR__ . '/config/configured_rule.neon'
        );
    }
}
