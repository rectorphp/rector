<?php declare(strict_types=1);

namespace Rector\Symfony\Tests\Rector\New_\StringToArrayArgumentProcessRector;

use Rector\Symfony\Rector\New_\StringToArrayArgumentProcessRector;
use Rector\Symfony\Tests\Rector\New_\StringToArrayArgumentProcessRector\Source\Process;
use Rector\Symfony\Tests\Rector\New_\StringToArrayArgumentProcessRector\Source\ProcessHelper;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class StringToArrayArgumentProcessRectorTest extends AbstractRectorTestCase
{
    public function test(): void
    {
        $this->doTestFiles([__DIR__ . '/Fixture/fixture.php.inc', __DIR__ . '/Fixture/fixture2.php.inc']);
    }

    protected function getRectorClass(): string
    {
        return StringToArrayArgumentProcessRector::class;
    }

    /**
     * @return mixed[]
     */
    protected function getRectorConfiguration(): array
    {
        return [
            '$processClass' => Process::class,
            '$processHelperClass' => ProcessHelper::class,
        ];
    }
}
