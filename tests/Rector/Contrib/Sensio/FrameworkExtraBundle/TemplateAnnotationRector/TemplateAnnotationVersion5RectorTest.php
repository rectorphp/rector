<?php declare(strict_types=1);

namespace Rector\Tests\Rector\Contrib\Sensio\FrameworkExtraBundle\TemplateAnnotationRector;

use Rector\Rector\Contrib\Sensio\FrameworkExtraBundle\TemplateAnnotationRector;
use Rector\Testing\PHPUnit\AbstractConfigurableRectorTestCase;

final class TemplateAnnotationVersion5RectorTest extends AbstractConfigurableRectorTestCase
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
            [__DIR__ . '/Wrong/Version5/wrong.php.inc', __DIR__ . '/Correct/Version5/correct.php.inc'],
            [__DIR__ . '/Wrong/Version5/wrong2.php.inc', __DIR__ . '/Correct/Version5/correct2.php.inc'],
            [__DIR__ . '/Wrong/Version5/wrong3.php.inc', __DIR__ . '/Correct/Version5/correct3.php.inc'],
            [__DIR__ . '/Wrong/Version5/wrong4.php.inc', __DIR__ . '/Correct/Version5/correct4.php.inc'],
            [__DIR__ . '/Wrong/Version5/wrong5.php.inc', __DIR__ . '/Correct/Version5/correct5.php.inc'],
            [__DIR__ . '/Wrong/Version5/wrong6.php.inc', __DIR__ . '/Correct/Version5/correct6.php.inc'],
            [__DIR__ . '/Wrong/Version5/wrong7.php.inc', __DIR__ . '/Correct/Version5/correct7.php.inc'],
            [__DIR__ . '/Wrong/Version5/wrong8.php.inc', __DIR__ . '/Correct/Version5/correct8.php.inc'],
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
        return __DIR__ . '/Source/version5.yml';
    }
}
