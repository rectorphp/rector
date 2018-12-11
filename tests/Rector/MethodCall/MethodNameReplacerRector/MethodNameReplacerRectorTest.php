<?php declare(strict_types=1);

namespace Rector\Tests\Rector\MethodCall\MethodNameReplacerRector;

use Nette\Utils\Html;
use Rector\Rector\MethodCall\MethodNameReplacerRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\Tests\Rector\MethodCall\MethodNameReplacerRector\Source\AbstractType;
use Rector\Tests\Rector\MethodCall\MethodNameReplacerRector\Source\FormMacros;

final class MethodNameReplacerRectorTest extends AbstractRectorTestCase
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
        ]);
    }

    protected function getRectorClass(): string
    {
        return MethodNameReplacerRector::class;
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
        ];
    }
}
