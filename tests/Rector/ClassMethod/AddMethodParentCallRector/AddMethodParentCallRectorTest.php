<?php declare(strict_types=1);

namespace Rector\Tests\Rector\ClassMethod\AddMethodParentCallRector;

use Rector\Rector\ClassMethod\AddMethodParentCallRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\Tests\Rector\ClassMethod\AddMethodParentCallRector\Source\ParentClassWithNewConstructor;

final class AddMethodParentCallRectorTest extends AbstractRectorTestCase
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
        yield [__DIR__ . '/Fixture/skip_already_has.php.inc'];
    }

    /**
     * @return mixed[]
     */
    protected function getRectorsWithConfiguration(): array
    {
        return [
            AddMethodParentCallRector::class => [
                '$methodsByParentTypes' => [
                    ParentClassWithNewConstructor::class => ['__construct'],
                ],
            ],
        ];
    }
}
