<?php

declare(strict_types=1);

namespace Rector\Caching\Tests\Config;

use Iterator;
use Rector\Caching\Config\FileHashComputer;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\Core\HttpKernel\RectorKernel;
use Symplify\PackageBuilder\Tests\AbstractKernelTestCase;

final class FileHashComputerTest extends AbstractKernelTestCase
{
    /**
     * @var FileHashComputer
     */
    private $fileHashComputer;

    protected function setUp(): void
    {
        $this->bootKernel(RectorKernel::class);
        $this->fileHashComputer = self::$container->get(FileHashComputer::class);
    }

    /**
     * @dataProvider provideDataForIdenticalHash()
     */
    public function testHashIsIdentical(string $firstConfig, string $secondConfig): void
    {
        $configAHash = $this->fileHashComputer->compute($firstConfig);
        $configBHash = $this->fileHashComputer->compute($secondConfig);

        $this->assertSame($configAHash, $configBHash);
    }

    public function provideDataForIdenticalHash(): Iterator
    {
        yield [__DIR__ . '/Source/config_content_a.yaml', __DIR__ . '/Source/config_content_b.yaml'];
        yield [__DIR__ . '/Source/Import/import_a.yaml', __DIR__ . '/Source/Import/import_b.yaml'];
    }

    public function testInvalidType(): void
    {
        $this->expectException(ShouldNotHappenException::class);
        $this->fileHashComputer->compute(__DIR__ . '/Source/file.php');
    }
}
