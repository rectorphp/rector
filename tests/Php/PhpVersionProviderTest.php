<?php

declare(strict_types=1);

namespace Rector\Core\Tests\Php;

use Iterator;
use Rector\Core\Exception\Configuration\InvalidConfigurationException;
use Rector\Core\Php\PhpVersionProvider;
use Rector\Testing\PHPUnit\AbstractTestCase;
use Symplify\SmartFileSystem\SmartFileInfo;

final class PhpVersionProviderTest extends AbstractTestCase
{
    /**
     * @doesNotPerformAssertions
     * @dataProvider provideValidConfigData()
     */
    public function testValidInput(SmartFileInfo $invalidFileInfo): void
    {
        $this->bootFromConfigFileInfos([$invalidFileInfo]);

        $phpVersionProvider = $this->getService(PhpVersionProvider::class);
        $phpVersionProvider->provide();
    }

    /**
     * @return Iterator<SmartFileInfo[]>
     */
    public function provideValidConfigData(): Iterator
    {
        yield [new SmartFileInfo(__DIR__ . '/config/valid_explicit_value.php')];
        yield [new SmartFileInfo(__DIR__ . '/config/valid_minus_value.php')];
    }

    /**
     * @dataProvider provideInvalidConfigData()
     */
    public function testInvalidInput(SmartFileInfo $invalidFileInfo): void
    {
        $this->expectException(InvalidConfigurationException::class);

        $this->bootFromConfigFileInfos([$invalidFileInfo]);

        $phpVersionProvider = $this->getService(PhpVersionProvider::class);
        $phpVersionProvider->provide();
    }

    /**
     * @return Iterator<SmartFileInfo[]>
     */
    public function provideInvalidConfigData(): Iterator
    {
        yield [new SmartFileInfo(__DIR__ . '/config/invalid_input.php')];
        yield [new SmartFileInfo(__DIR__ . '/config/invalid_string_input.php')];
        yield [new SmartFileInfo(__DIR__ . '/config/invalid_number_input.php')];
        yield [new SmartFileInfo(__DIR__ . '/config/invalid_php_4_number.php')];
    }
}
