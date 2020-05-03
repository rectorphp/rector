<?php

declare(strict_types=1);

namespace Rector\PHPStanExtensions\Tests\Rule\SeeAnnotationToTestRule;

use Iterator;
use PHPStan\Rules\Rule;
use Rector\PHPStanExtensions\Rule\SeeAnnotationToTestRule;
use Rector\PHPStanExtensions\Tests\AbstractServiceAwareRuleTestCase;
use Rector\PHPStanExtensions\Tests\Rule\SeeAnnotationToTestRule\Fixture\ClassMissingDocBlockRector;
use Rector\PHPStanExtensions\Tests\Rule\SeeAnnotationToTestRule\Fixture\ClassMissingSeeAnnotationRector;

final class SeeAnnotationToTestRuleTest extends AbstractServiceAwareRuleTestCase
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
        $errorMessage = sprintf(SeeAnnotationToTestRule::ERROR_MESSAGE, ClassMissingDocBlockRector::class);
        yield [__DIR__ . '/Fixture/ClassMissingDocBlockRector.php', [[$errorMessage, 12]]];

        $errorMessage = sprintf(SeeAnnotationToTestRule::ERROR_MESSAGE, ClassMissingSeeAnnotationRector::class);
        yield [__DIR__ . '/Fixture/ClassMissingSeeAnnotationRector.php', [[$errorMessage, 15]]];
    }

    protected function getRule(): Rule
    {
        return $this->getRuleFromConfig(
            SeeAnnotationToTestRule::class,
            __DIR__ . '/../../../config/phpstan-extensions.neon'
        );
    }
}
