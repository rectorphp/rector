<?php

declare(strict_types=1);

namespace Rector\CodingStyle\Tests\Rector\ClassMethod\MakeInheritedMethodVisibilitySameAsParentRector;

use Iterator;
use Rector\CodingStyle\Rector\ClassMethod\MakeInheritedMethodVisibilitySameAsParentRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;
use Symplify\SmartFileSystem\SmartFileInfo;

final class SkipParentConstructOverrideInPHP72Test extends AbstractRectorTestCase
{
    /**
     * @requires PHP 7.2
     * @see https://phpunit.readthedocs.io/en/8.3/incomplete-and-skipped-tests.html#incomplete-and-skipped-tests-requires-tables-api
     *
     * @dataProvider provideData()
     */
    public function test(SmartFileInfo $fileInfo): void
    {
        $this->doTestFileInfo($fileInfo);
    }

    public function provideData(): Iterator
    {
        return $this->yieldFilesFromDirectory(__DIR__ . '/FixtureForPhp72');
    }

    protected function getRectorClass(): string
    {
        return MakeInheritedMethodVisibilitySameAsParentRector::class;
    }

    protected function getPhpVersion(): string
    {
        return '7.2';
    }
}
