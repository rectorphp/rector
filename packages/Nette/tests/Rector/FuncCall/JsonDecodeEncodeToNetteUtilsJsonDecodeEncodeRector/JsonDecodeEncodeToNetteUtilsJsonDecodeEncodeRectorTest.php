<?php declare(strict_types=1);

namespace Rector\Nette\Tests\Rector\FuncCall\JsonDecodeEncodeToNetteUtilsJsonDecodeEncodeRector;

use Rector\Nette\Rector\FuncCall\JsonDecodeEncodeToNetteUtilsJsonDecodeEncodeRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class JsonDecodeEncodeToNetteUtilsJsonDecodeEncodeRectorTest extends AbstractRectorTestCase
{
    public function test(): void
    {
        $this->doTestFiles([__DIR__ . '/Fixture/json_decode.php.inc', __DIR__ . '/Fixture/json_encode.php.inc']);
    }

    protected function getRectorClass(): string
    {
        return JsonDecodeEncodeToNetteUtilsJsonDecodeEncodeRector::class;
    }
}
