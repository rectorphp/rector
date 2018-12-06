<?php declare(strict_types=1);

namespace Rector\Php\Tests\Rector\Assign\AssignArrayToStringRector;

use Rector\Php\Rector\Assign\AssignArrayToStringRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class AssignArrayToStringRectorTest extends AbstractRectorTestCase
{
    public function test(): void
    {
        $this->doTestFiles([
            __DIR__ . '/Fixture/fixture.php.inc',
            __DIR__ . '/Fixture/fixture2.php.inc',
            __DIR__ . '/Fixture/fixture3.php.inc',
            __DIR__ . '/Fixture/fixture4.php.inc',
            __DIR__ . '/Fixture/fixture5.php.inc',
            __DIR__ . '/Fixture/fixture6.php.inc',
            __DIR__ . '/Fixture/fixture7.php.inc',
            __DIR__ . '/Fixture/fixture8.php.inc',
            __DIR__ . '/Fixture/fixture9.php.inc',
        ]);
    }

    public function getRectorClass(): string
    {
        return AssignArrayToStringRector::class;
    }
}
