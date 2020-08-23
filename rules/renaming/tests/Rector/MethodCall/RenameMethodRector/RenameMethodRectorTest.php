<?php

declare(strict_types=1);

namespace Rector\Renaming\Tests\Rector\MethodCall\RenameMethodRector;

use Iterator;
use Nette\Utils\Html;
use Rector\Core\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\Renaming\Rector\MethodCall\RenameMethodRector;
use Rector\Renaming\Tests\Rector\MethodCall\RenameMethodRector\Fixture\SkipSelfMethodRename;
use Rector\Renaming\Tests\Rector\MethodCall\RenameMethodRector\Source\AbstractType;
use Rector\Renaming\ValueObject\MethodCallRename;
use Symplify\SmartFileSystem\SmartFileInfo;

final class RenameMethodRectorTest extends AbstractRectorTestCase
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
     * @return mixed[]
     */
    protected function getRectorsWithConfiguration(): array
    {
        return [
            RenameMethodRector::class => [
                RenameMethodRector::OLD_TO_NEW_METHODS_BY_CLASS => [
                    new MethodCallRename(AbstractType::class, 'setDefaultOptions', 'configureOptions'),
                    new MethodCallRename(Html::class, 'add', 'addHtml'),
                    new MethodCallRename('*Presenter', 'run', '__invoke'),
                    new MethodCallRename(SkipSelfMethodRename::class, 'preventPHPStormRefactoring', 'gone'),
                ],
            ],
        ];
    }
}
