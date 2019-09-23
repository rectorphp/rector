<?php declare(strict_types=1);

namespace Rector\Renaming\Tests\Rector\MethodCall\RenameMethodRector;

use Nette\Utils\Html;
use Rector\Renaming\Rector\MethodCall\RenameMethodRector;
use Rector\Renaming\Tests\Rector\MethodCall\RenameMethodRector\Source\AbstractType;
use Rector\Renaming\Tests\Rector\MethodCall\RenameMethodRector\Source\FormMacros;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class RenameMethodRectorTest extends AbstractRectorTestCase
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
        yield [__DIR__ . '/Fixture/fixture6.php.inc'];
        yield [__DIR__ . '/Fixture/under_anonymous_class.php.inc'];
        yield [__DIR__ . '/Fixture/SomeClass.php'];
        yield [__DIR__ . '/Fixture/nette_to_symfony_presenter.php.inc'];
    }

    /**
     * @return mixed[]
     */
    protected function getRectorsWithConfiguration(): array
    {
        return [
            RenameMethodRector::class => [
                '$oldToNewMethodsByClass' => [
                    AbstractType::class => [
                        'setDefaultOptions' => 'configureOptions',
                    ],
                    Html::class => [
                        'add' => 'addHtml',
                        'addToArray' => [
                            'name' => 'addHtmlArray',
                            'array_key' => 'hi',
                        ],
                    ],
                    FormMacros::class => [
                        'renderFormBegin' => ['Nette\Bridges\FormsLatte\Runtime', 'renderFormBegin'],
                    ],
                    '*Presenter' => [
                        'run' => '__invoke',
                    ],
                    'PHPUnit\Framework\TestClass' => [
                        'setExpectedException' => 'expectedException',
                        'setExpectedExceptionRegExp' => 'expectedException',
                    ],
                ],
            ],
        ];
    }
}
