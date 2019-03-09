<?php declare(strict_types=1);

namespace Rector\Tests\Rector\MethodCall\StaticMethodNameReplacerRector;

use Nette\Utils\Html;
use Rector\Rector\MethodCall\StaticMethodNameReplacerRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\Tests\Rector\MethodCall\RenameMethodRector\Source\FormMacros;

final class StaticMethodNameReplacerRectorTest extends AbstractRectorTestCase
{
    public function test(): void
    {
        $this->doTestFiles([__DIR__ . '/Fixture/fixture.php.inc', __DIR__ . '/Fixture/fixture2.php.inc']);
    }

    protected function getRectorClass(): string
    {
        return StaticMethodNameReplacerRector::class;
    }

    /**
     * @return mixed[]
     */
    protected function getRectorConfiguration(): array
    {
        return [
            Html::class => ['add' => 'addHtml'],
            FormMacros::class => ['renderFormBegin' => ['Nette\Bridges\FormsLatte\Runtime', 'renderFormBegin']],
        ];
    }
}
