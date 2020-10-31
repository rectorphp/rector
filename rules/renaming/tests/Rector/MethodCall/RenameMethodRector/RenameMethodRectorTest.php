<?php

declare(strict_types=1);

namespace Rector\Renaming\Tests\Rector\MethodCall\RenameMethodRector;

use Iterator;
use Nette\Utils\Html;
use Rector\Renaming\Rector\MethodCall\RenameMethodRector;
use Rector\Renaming\Tests\Rector\MethodCall\RenameMethodRector\Fixture\SkipSelfMethodRename;
use Rector\Renaming\Tests\Rector\MethodCall\RenameMethodRector\Source\AbstractType;
use Rector\Renaming\ValueObject\MethodCallRename;
use Rector\Renaming\ValueObject\MethodCallRenameWithArrayKey;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;
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
     * @return array<string, mixed[]>
     */
    protected function getRectorsWithConfiguration(): array
    {
        return [
            RenameMethodRector::class => [
                RenameMethodRector::METHOD_CALL_RENAMES => [
                    new MethodCallRename(AbstractType::class, 'setDefaultOptions', 'configureOptions'),
                    new MethodCallRename(Html::class, 'add', 'addHtml'),
                    new MethodCallRename('*Presenter', 'run', '__invoke'),
                    new MethodCallRename(SkipSelfMethodRename::class, 'preventPHPStormRefactoring', 'gone'),
                    // with array key
                    new MethodCallRenameWithArrayKey(Html::class, 'addToArray', 'addToHtmlArray', 'hey'),
                ],
            ],
        ];
    }
}
