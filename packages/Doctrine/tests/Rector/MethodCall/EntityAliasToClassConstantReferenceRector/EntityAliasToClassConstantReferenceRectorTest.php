<?php declare(strict_types=1);

namespace Rector\Doctrine\Tests\Rector\MethodCall\EntityAliasToClassConstantReferenceRector;

use Iterator;
use Rector\Doctrine\Rector\MethodCall\EntityAliasToClassConstantReferenceRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class EntityAliasToClassConstantReferenceRectorTest extends AbstractRectorTestCase
{
    /**
     * @dataProvider provideDataForTest()
     */
    public function test(string $file): void
    {
        $this->doTestFile($file);
    }

    public function provideDataForTest(): Iterator
    {
        yield [__DIR__ . '/Fixture/fixture.php.inc'];
    }

    /**
     * @return mixed[]
     */
    protected function getRectorsWithConfiguration(): array
    {
        return [
            EntityAliasToClassConstantReferenceRector::class => [
                '$aliasesToNamespaces' => [
                    'App' => 'App\Entity',
                ],
            ],
        ];
    }
}
