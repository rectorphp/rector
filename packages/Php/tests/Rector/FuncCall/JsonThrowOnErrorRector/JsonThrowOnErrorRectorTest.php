<?php declare(strict_types=1);

namespace Rector\Php\Tests\Rector\FuncCall\JsonThrowOnErrorRector;

use Rector\Php\Rector\FuncCall\JsonThrowOnErrorRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class JsonThrowOnErrorRectorTest extends AbstractRectorTestCase
{
    public function test(): void
    {
        $this->doTestFiles([__DIR__ . '/Fixture/fixture.php.inc']);
    }

    public function getRectorClass(): string
    {
        return JsonThrowOnErrorRector::class;
    }
}
