<?php declare(strict_types=1);

namespace Rector\Tests\Rector\Contrib\Symfony\HttpKernel\TemplateAnnotationRector;

use Rector\Rector\Contrib\Symfony\HttpKernel\TemplateAnnotationRector;
use Rector\Testing\PHPUnit\AbstractConfigurableRectorTestCase;

final class TemplateAnnotationRectorTest extends AbstractConfigurableRectorTestCase
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
            [__DIR__ . '/Wrong/wrong2.php.inc', __DIR__ . '/Correct/correct2.php.inc'],
            [__DIR__ . '/Wrong/wrong3.php.inc', __DIR__ . '/Correct/correct3.php.inc'],
            [__DIR__ . '/Wrong/wrong4.php.inc', __DIR__ . '/Correct/correct4.php.inc'],
            [__DIR__ . '/Wrong/wrong5.php.inc', __DIR__ . '/Correct/correct5.php.inc'],
            [__DIR__ . '/Wrong/wrong6.php.inc', __DIR__ . '/Correct/correct6.php.inc'],
        ];
    }

    /**
     * @return string[]
     */
    protected function getRectorClasses(): array
    {
        return [TemplateAnnotationRector::class];
    }

    protected function provideConfig(): string
    {
        return __DIR__ . '/Source/rector.yml';
    }
}
