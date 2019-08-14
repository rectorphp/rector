<?php declare(strict_types=1);

namespace Rector\Tests\Rector\MethodBody\FluentReplaceRector;

use Rector\Rector\MethodBody\FluentReplaceRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\Tests\Rector\MethodBody\FluentReplaceRector\Source\FluentInterfaceClass;

final class FluentReplaceRectorTest extends AbstractRectorTestCase
{
    public function test(): void
    {
        $this->doTestFiles([
            __DIR__ . '/Fixture/fixture.php.inc',
            __DIR__ . '/Fixture/some_command.php.inc',
            __DIR__ . '/Fixture/multiple_some_command.php.inc',
            __DIR__ . '/Fixture/skip_date_time_modify_command.php.inc',
        ]);
    }

    /**
     * @return mixed[]
     */
    protected function getRectorsWithConfiguration(): array
    {
        return [
            FluentReplaceRector::class => [
                '$classesToDefluent' => [FluentInterfaceClass::class, '*Command'],
            ],
        ];
    }
}
