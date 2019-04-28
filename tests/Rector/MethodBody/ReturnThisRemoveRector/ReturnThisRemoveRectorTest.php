<?php declare(strict_types=1);

namespace Rector\Tests\Rector\MethodBody\ReturnThisRemoveRector;

use Rector\Rector\MethodBody\ReturnThisRemoveRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class ReturnThisRemoveRectorTest extends AbstractRectorTestCase
{
    public function test(): void
    {
        $this->doTestFiles([
            __DIR__ . '/Fixture/fixture.php.inc',
            __DIR__ . '/Fixture/fixture2.php.inc',
            __DIR__ . '/Fixture/fixture3.php.inc',
        ]);
    }

    /**
     * @return mixed[]
     */
    protected function getRectorsWithConfiguration(): array
    {
        return [
            ReturnThisRemoveRector::class => [
                '$classesToDefluent' => [
                    'Rector\Tests\Rector\MethodBody\ReturnThisRemoveRector\SomeClass',
                    'Rector\Tests\Rector\MethodBody\ReturnThisRemoveRector\SomeClassWithReturnAnnotations',
                ],
            ],
        ];
    }
}
