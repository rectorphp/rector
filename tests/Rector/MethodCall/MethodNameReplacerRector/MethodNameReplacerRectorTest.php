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
            __DIR__ . '/Wrong/wrong.php.inc',
            __DIR__ . '/Wrong/wrong2.php.inc',
            __DIR__ . '/Wrong/wrong3.php.inc',
            __DIR__ . '/Wrong/wrong4.php.inc',
            __DIR__ . '/Wrong/wrong5.php.inc',
            __DIR__ . '/Wrong/wrong6.php.inc',
            __DIR__ . '/Wrong/SomeClass.php',
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
            AbstractType::class => ['setDefaultOptions' => 'configureOptions'],
            Html::class => ['add' => 'addHtml'],
            FormMacros::class => ['renderFormBegin' => ['Nette\Bridges\FormsLatte\Runtime', 'renderFormBegin']],
        ];
    }
}
