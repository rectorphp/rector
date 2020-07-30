<?php

declare(strict_types=1);

namespace Rector\Renaming\Tests\Rector\Namespace_\RenameNamespaceRector;

use Iterator;
use Rector\Core\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\Renaming\Rector\Namespace_\RenameNamespaceRector;
use Symplify\SmartFileSystem\SmartFileInfo;

final class RenameNamespaceRectorTest extends AbstractRectorTestCase
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
            RenameNamespaceRector::class => [
                RenameNamespaceRector::OLD_TO_NEW_NAMESPACES => [
                    'OldNamespace' => 'NewNamespace',
                    'OldNamespaceWith\OldSplitNamespace' => 'NewNamespaceWith\NewSplitNamespace',
                    'Old\Long\AnyNamespace' => 'Short\AnyNamespace',
                    'PHPUnit_Framework_' => 'PHPUnit\Framework\\',
                ],
            ],
        ];
    }
}
