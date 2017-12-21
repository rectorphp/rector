<?php declare(strict_types=1);

namespace Rector\Tests\Rector\Contrib\PHPUnit\SpecificMethod\AssertTrueIssetToHasObjectAttributeRector;

use Rector\Rector\Contrib\PHPUnit\SpecificMethod\AssertTrueIssetToObjectHasAttributeRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class AssertTrueIssetToObjectHasAttributeRectorTest extends AbstractRectorTestCase
{
    /**
     * @dataProvider provideWrongToFixedFiles()
     */
    public function test(string $wrong, string $fixed): void
    {
        $this->doTestFileMatchesExpectedContent($wrong, $fixed);
    }

    /**
     * @return string[][]
     */
    public function provideWrongToFixedFiles(): array
    {
        return [
            [__DIR__ . '/Wrong/wrong.php.inc', __DIR__ . '/Correct/correct.php.inc'],
        ];
    }

    /**
     * @return string[]
     */
    protected function getRectorClasses(): array
    {
        return [AssertTrueIssetToObjectHasAttributeRector::class];
    }
}
