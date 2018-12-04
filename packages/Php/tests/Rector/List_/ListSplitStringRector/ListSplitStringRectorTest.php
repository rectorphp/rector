<?php declare(strict_types=1);

namespace Rector\Php\Tests\Rector\List_\ListSplitStringRector;

use Rector\Php\Rector\List_\ListSplitStringRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class ListSplitStringRectorTest extends AbstractRectorTestCase
{
    public function test(): void
    {
        $this->doTestFiles([__DIR__ . '/Wrong/wrong.php.inc']);
    }

    public function getRectorClass(): string
    {
        return ListSplitStringRector::class;
    }
}
