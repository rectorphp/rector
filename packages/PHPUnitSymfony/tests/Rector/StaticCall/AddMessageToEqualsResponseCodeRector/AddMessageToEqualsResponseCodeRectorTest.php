<?php declare(strict_types=1);

namespace Rector\PHPUnitSymfony\Tests\Rector\StaticCall\AddMessageToEqualsResponseCodeRector;

use Rector\PHPUnitSymfony\Rector\StaticCall\AddMessageToEqualsResponseCodeRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class AddMessageToEqualsResponseCodeRectorTest extends AbstractRectorTestCase
{
    public function test(): void
    {
        $this->doTestFiles([__DIR__ . '/Fixture/fixture.php.inc', __DIR__ . '/Fixture/method_call.php.inc']);
    }

    protected function getRectorClass(): string
    {
        return AddMessageToEqualsResponseCodeRector::class;
    }
}
