<?php declare(strict_types=1);

namespace Rector\Tests\Rector\MethodCall\RenameMethodCallRector;

use Nette\Utils\Html;
use Rector\Rector\MethodCall\RenameMethodCallRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\Tests\Rector\MethodCall\RenameMethodCallRector\Source\ClassMethodToBeSkipped;

final class RenameMethodCallRectorTest extends AbstractRectorTestCase
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
    }

    /**
     * @return mixed[]
     */
    protected function getRectorsWithConfiguration(): array
    {
        return [
            RenameMethodCallRector::class => [
                '$oldToNewMethodsByClass' => [
                    Html::class => [
                        'add' => 'addHtml',
                        'addToArray' => [
                            'name' => 'addHtmlArray',
                            'array_key' => 'hi',
                        ],
                    ],
                    ClassMethodToBeSkipped::class => [
                        'createHtml' => 'testHtml',
                    ],
                ],
            ],
        ];
    }
}
