<?php declare(strict_types=1);

namespace Rector\Php\Tests\Rector\String_\SensitiveHereNowDocRector;

use Rector\Php\Rector\String_\SensitiveHereNowDocRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class SensitiveHereNowDocRectorTest extends AbstractRectorTestCase
{
    public function test(): void
    {
        $this->doTestFiles([__DIR__ . '/Fixture/fixture.php.inc']);
    }

    public function getRectorClass(): string
    {
        return SensitiveHereNowDocRector::class;
    }
}
