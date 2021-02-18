<?php
declare(strict_types=1);

namespace Rector\Core\Tests\Issues\Issue5612\DoNotSetBadParamType;

use Iterator;
use Rector\Privatization\Rector\Class_\FinalizeClassesWithoutChildrenRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;
use Symplify\SmartFileSystem\SmartFileInfo;

final class DoNotSetBadParamTypeWithRuleTest extends AbstractRectorTestCase
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
        return $this->yieldFilesFromDirectory(__DIR__ . '/Fixture/Rule');
    }

    protected function provideConfigFilePath(): string
    {
        return __DIR__ . '/config/rule_config.php';
    }
}
