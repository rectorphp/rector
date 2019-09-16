<?php declare(strict_types=1);

namespace Rector\Tests\Rector\Class_\ParentClassToTraitsRector;

use Rector\Rector\Class_\ParentClassToTraitsRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\Tests\Rector\Class_\ParentClassToTraitsRector\Source\AnotherParentObject;
use Rector\Tests\Rector\Class_\ParentClassToTraitsRector\Source\ParentObject;
use Rector\Tests\Rector\Class_\ParentClassToTraitsRector\Source\SecondTrait;
use Rector\Tests\Rector\Class_\ParentClassToTraitsRector\Source\SomeTrait;

final class ParentClassToTraitsRectorTest extends AbstractRectorTestCase
{
    /**
     * @dataProvider provideDataForTest()
     */
    public function test(string $file): void
    {
        $this->doTestFile($file);
    }

    /**
     * @return string[]
     */
    public function provideDataForTest(): iterable
    {
        yield [__DIR__ . '/Fixture/fixture.php.inc'];
        yield [__DIR__ . '/Fixture/fixture2.php.inc'];
        yield [__DIR__ . '/Fixture/fixture3.php.inc'];
        yield [__DIR__ . '/Fixture/fixture4.php.inc'];
        yield [__DIR__ . '/Fixture/fixture5.php.inc'];
    }

    /**
     * @return mixed[]
     */
    protected function getRectorsWithConfiguration(): array
    {
        return [
            ParentClassToTraitsRector::class => [
                '$parentClassToTraits' => [
                    ParentObject::class => [SomeTrait::class],
                    AnotherParentObject::class => [SomeTrait::class, SecondTrait::class],
                ],
            ],
        ];
    }
}
