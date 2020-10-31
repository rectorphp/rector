<?php

declare(strict_types=1);

namespace Rector\__Package__\Tests\Rector\__Category__\__Name__;

use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class __Name__Test extends AbstractRectorTestCase
{
    /**
     * @dataProvider provideData()
     */
    public function test(\Symplify\SmartFileSystem\SmartFileInfo $fileInfo): void
    {
        $this->doTestFileInfo($fileInfo);
    }

    public function provideData(): \Iterator
    {
        return $this->yieldFilesFromDirectory(__DIR__ . '/Fixture');
    }

    /**
     * @return mixed[]
     */
    protected function getRectorsWithConfiguration(): array
    {
        return [
            \Rector\__Package__\Rector\__Category__\__Name__::class =>
                __TestRuleConfiguration__
        ];
    }
}
