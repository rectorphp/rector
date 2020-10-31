<?php

declare(strict_types=1);

namespace Rector\Renaming\Tests\Rector\StaticCall\RenameStaticMethodRector;

use Iterator;
use Nette\Utils\Html;
use Rector\Renaming\Rector\StaticCall\RenameStaticMethodRector;
use Rector\Renaming\Tests\Rector\StaticCall\RenameStaticMethodRector\Source\FormMacros;
use Rector\Renaming\ValueObject\RenameStaticMethod;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;
use Symplify\SmartFileSystem\SmartFileInfo;

final class RenameStaticMethodRectorTest extends AbstractRectorTestCase
{
    /**
     * @dataProvider provideData()
     */
    public function test(SmartFileInfo $fileInfo): void
    {
        $this->doTestFileInfo($fileInfo);
    }

    public function provideData(): Iterator
    {
        return $this->yieldFilesFromDirectory(__DIR__ . '/Fixture');
    }

    /**
     * @return array<string, mixed[]>
     */
    protected function getRectorsWithConfiguration(): array
    {
        return [
            RenameStaticMethodRector::class => [
                RenameStaticMethodRector::OLD_TO_NEW_METHODS_BY_CLASSES => [
                    new RenameStaticMethod(Html::class, 'add', Html::class, 'addHtml'),
                    new RenameStaticMethod(
                        FormMacros::class,
                        'renderFormBegin',
                        'Nette\Bridges\FormsLatte\Runtime',
                        'renderFormBegin'
                    ),
                ],
            ],
        ];
    }
}
