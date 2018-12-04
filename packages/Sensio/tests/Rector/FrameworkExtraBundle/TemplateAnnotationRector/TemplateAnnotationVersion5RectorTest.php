<?php declare(strict_types=1);

namespace Rector\Sensio\Tests\Rector\FrameworkExtraBundle\TemplateAnnotationRector;

use Rector\Testing\PHPUnit\AbstractRectorTestCase;

/**
 * @covers \Rector\Sensio\Rector\FrameworkExtraBundle\TemplateAnnotationRector
 */
final class TemplateAnnotationVersion5RectorTest extends AbstractRectorTestCase
{
    public function test(): void
    {
        $this->doTestFiles([
            __DIR__ . '/Correct/Version5/correct.php.inc',
            __DIR__ . '/Correct/Version5/correct2.php.inc',
            __DIR__ . '/Correct/Version5/correct3.php.inc',
            __DIR__ . '/Correct/Version5/correct4.php.inc',
        ]);
    }

    protected function provideConfig(): string
    {
        return __DIR__ . '/config-version5.yml';
    }
}
