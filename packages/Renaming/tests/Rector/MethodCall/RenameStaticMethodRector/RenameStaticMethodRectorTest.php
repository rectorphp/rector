<?php

declare(strict_types=1);

namespace Rector\Renaming\Tests\Rector\MethodCall\RenameStaticMethodRector;

use Iterator;
use Nette\Utils\Html;
use Rector\Renaming\Rector\MethodCall\RenameStaticMethodRector;
use Rector\Renaming\Tests\Rector\MethodCall\RenameStaticMethodRector\Source\FormMacros;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class RenameStaticMethodRectorTest extends AbstractRectorTestCase
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
        return $this->yieldFilesFromDirectory(__DIR__ . '/Fixture');
    }

    /**
     * @return mixed[]
     */
    protected function getRectorsWithConfiguration(): array
    {
        return [
            RenameStaticMethodRector::class => [
                '$oldToNewMethodByClasses' => [
                    Html::class => [
                        'add' => 'addHtml',
                    ],
                    FormMacros::class => [
                        'renderFormBegin' => ['Nette\Bridges\FormsLatte\Runtime', 'renderFormBegin'],
                    ],
                ],
            ],
        ];
    }
}
