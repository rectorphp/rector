<?php declare(strict_types=1);

namespace Rector\Sensio\Tests\Rector\FrameworkExtraBundle\TemplateAnnotationRector;

use Rector\Sensio\Rector\FrameworkExtraBundle\TemplateAnnotationRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class TemplateAnnotationVersion3RectorTest extends AbstractRectorTestCase
{
    public function test(): void
    {
        $this->doTestFiles([
            __DIR__ . '/Fixture/Version3/fixture.php.inc',
            __DIR__ . '/Fixture/Version3/fixture2.php.inc',
            __DIR__ . '/Fixture/Version3/fixture3.php.inc',
            __DIR__ . '/Fixture/Version3/fixture4.php.inc',
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
