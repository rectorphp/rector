<?php declare(strict_types=1);

namespace Rector\Sensio\Tests\Rector\FrameworkExtraBundle\TemplateAnnotationRector;

use Rector\Sensio\Rector\FrameworkExtraBundle\TemplateAnnotationRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class TemplateAnnotationVersion3RectorTest extends AbstractRectorTestCase
{
    public function test(): void
    {
        $this->doTestFiles([
            __DIR__ . '/Correct/Version3/correct.php.inc',
            __DIR__ . '/Correct/Version3/correct2.php.inc',
            __DIR__ . '/Correct/Version3/correct3.php.inc',
            __DIR__ . '/Correct/Version3/correct4.php.inc',
        ]);
    }

    protected function getRectorClass(): string
    {
        return TemplateAnnotationRector::class;
    }

    /**
     * @return mixed[]
     */
    protected function getRectorConfiguration(): array
    {
        return ['$version' => 3];
    }
}
