<?php

declare(strict_types=1);

namespace Rector\Core\Tests\Issues\ReturnEmptyNodes;

use Iterator;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;
use Symplify\SmartFileSystem\SmartFileInfo;

final class ReturnEmptyNodesTest extends AbstractRectorTestCase
{
    /**
     * @dataProvider provideData()
     */
    public function test(SmartFileInfo $fileInfo): void
    {
        $this->expectExceptionMessage(
            <<<CODE_SAMPLE
Array of nodes must not be empty, ensure Rector\Core\Tests\Issues\ReturnEmptyNodes\Source\ReturnEmptyStmtsRector->refactor() returns non-empty array for Nodes.

You can also either return null for no change:

    return null;

or remove the Node if not needed via

    \$this->removeNode(\$node);
    return \$node;
CODE_SAMPLE
        );
        $this->expectException(ShouldNotHappenException::class);
        $this->doTestFileInfo($fileInfo);
    }

    /**
     * @return Iterator<SmartFileInfo>
     */
    public function provideData(): Iterator
    {
        return $this->yieldFilesFromDirectory(__DIR__ . '/Fixture');
    }

    public function provideConfigFilePath(): string
    {
        return __DIR__ . '/config/configured_rule.php';
    }
}
