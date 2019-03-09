<?php declare(strict_types=1);

namespace Rector\Tests\Rector\MethodCall\RenameMethodRector;

use Nette\Utils\Html;
use Rector\Rector\MethodCall\RenameMethodRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\Tests\Rector\MethodCall\RenameMethodRector\Source\AbstractType;
use Rector\Tests\Rector\MethodCall\RenameMethodRector\Source\FormMacros;

final class RenameMethodRectorTest extends AbstractRectorTestCase
{
    public function test(): void
    {
        $this->doTestFiles([
            __DIR__ . '/Fixture/fixture.php.inc',
            __DIR__ . '/Fixture/fixture2.php.inc',
            __DIR__ . '/Fixture/fixture3.php.inc',
            __DIR__ . '/Fixture/fixture4.php.inc',
            __DIR__ . '/Fixture/fixture5.php.inc',
            __DIR__ . '/Fixture/fixture6.php.inc',
            __DIR__ . '/Fixture/SomeClass.php',
            __DIR__ . '/Fixture/nette_to_symfony_presenter.php.inc',
        ]);
    }

    protected function getRectorClass(): string
    {
        return RenameMethodRector::class;
    }

    /**
     * @return mixed[]
     */
    protected function getRectorConfiguration(): array
    {
        return [
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
        ];
    }
}
