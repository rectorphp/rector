<?php declare(strict_types=1);

namespace Rector\Tests\Rector\RectorOrder;

use Rector\Rector\Contrib\PHPUnit\SpecificMethod\AssertComparisonToSpecificMethodRector;
use Rector\Rector\Contrib\PHPUnit\SpecificMethod\AssertFalseStrposToContainsRector;
use Rector\Rector\Contrib\PHPUnit\SpecificMethod\AssertSameBoolNullToSpecificMethodRector;
use Rector\Rector\Contrib\PHPUnit\SpecificMethod\AssertTrueFalseToSpecificMethodRector;
use Rector\Testing\PHPUnit\AbstractConfigurableRectorTestCase;

/**
 * Covers https://github.com/rectorphp/rector/pull/266#issuecomment-355725764
 */
final class RectorOrderTest extends AbstractConfigurableRectorTestCase
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

    protected function provideConfig(): string
    {
        return __DIR__ . '/config/rector.yml';
    }

    /**
     * @return string[]
     */
    protected function getRectorClasses(): array
    {
        return [
            AssertSameBoolNullToSpecificMethodRector::class,
            AssertComparisonToSpecificMethodRector::class,
            AssertTrueFalseToSpecificMethodRector::class,
            AssertFalseStrposToContainsRector::class,
        ];
    }
}
