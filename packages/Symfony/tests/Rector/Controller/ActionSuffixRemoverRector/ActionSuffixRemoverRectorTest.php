<?php declare(strict_types=1);

namespace Rector\Symfony\Tests\Rector\Controller\ActionSuffixRemoverRector;

use Rector\Symfony\Rector\Controller\ActionSuffixRemoverRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class ActionSuffixRemoverRectorTest extends AbstractRectorTestCase
{
    public function test(): void
    {
        $this->doTestFiles([
            //            __DIR__ . '/Fixture/fixture.php.inc',
            __DIR__ . '/Fixture/fixture2.php.inc',
        ]);
    }

    public function getRectorClass(): string
    {
        return ActionSuffixRemoverRector::class;
    }
}
