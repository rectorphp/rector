<?php declare(strict_types=1);

namespace Rector\Tests\Rector\Annotation\RenameAnnotationRector;

use Rector\Rector\Annotation\RenameAnnotationRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class RenameAnnotationRectorTest extends AbstractRectorTestCase
{
    public function test(): void
    {
        $this->doTestFiles([__DIR__ . '/Fixture/fixture.php.inc']);
    }

    /**
     * @return mixed[]
     */
    protected function getRectorsWithConfiguration(): array
    {
        return [
            RenameAnnotationRector::class => [
                '$classToAnnotationMap' => [
                    'PHPUnit\Framework\TestCase' => [
                        'scenario' => 'test',
                    ],
                ],
            ],
        ];
    }
}
