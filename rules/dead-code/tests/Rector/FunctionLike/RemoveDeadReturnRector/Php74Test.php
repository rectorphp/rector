<?php

declare(strict_types=1);

namespace Rector\DeadCode\Tests\Rector\FunctionLike\RemoveDeadReturnRector;

use Iterator;
use Rector\Core\ValueObject\PhpVersionFeature;
use Rector\DeadCode\Rector\FunctionLike\RemoveDeadReturnRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;
use Symplify\SmartFileSystem\SmartFileInfo;

/**
 * @requires PHP 7.4
 */
final class Php74Test extends AbstractRectorTestCase
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
        return $this->yieldFilesFromDirectory(__DIR__ . '/FixturePhp74');
    }

    protected function getPhpVersion(): string
    {
        return PhpVersionFeature::ARROW_FUNCTION;
    }

    protected function getRectorClass(): string
    {
        return RemoveDeadReturnRector::class;
    }
}
