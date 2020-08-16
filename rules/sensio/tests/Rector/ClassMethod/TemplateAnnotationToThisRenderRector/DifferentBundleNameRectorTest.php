<?php

declare(strict_types=1);

namespace Rector\Sensio\Tests\Rector\ClassMethod\TemplateAnnotationToThisRenderRector;

use Iterator;
use Nette\Utils\FileSystem;
use Rector\Core\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\Sensio\Rector\ClassMethod\TemplateAnnotationToThisRenderRector;
use Symplify\SmartFileSystem\SmartFileInfo;

final class DifferentBundleNameRectorTest extends AbstractRectorTestCase
{
    /**
     * @dataProvider provideData()
     */
    public function test(SmartFileInfo $fileInfo): void
    {
        // prepare bundle path
        $originalBundleFilePath = __DIR__ . '/FixtureDifferentBundleName/SomeActionBundle/DifferentNameBundle.php';
        $temporaryBundleFilePath = $this->getTempPath() . '/DifferentNameBundle.php';
        FileSystem::copy($originalBundleFilePath, $temporaryBundleFilePath, true);

        $this->doTestFileInfo($fileInfo);

        FileSystem::delete($temporaryBundleFilePath);
    }

    public function provideData(): Iterator
    {
        return $this->yieldFilesFromDirectory(__DIR__ . '/FixtureDifferentBundleName');
    }

    protected function getRectorClass(): string
    {
        return TemplateAnnotationToThisRenderRector::class;
    }
}
