<?php

declare(strict_types=1);

namespace Rector\Core\Tests\Php;

use Iterator;
use Rector\Core\Exception\Configuration\InvalidConfigurationException;
use Rector\Core\Php\PhpVersionProvider;
use Rector\Testing\PHPUnit\AbstractTestCase;

final class PhpVersionProviderTest extends AbstractTestCase
{
    /**
     * @doesNotPerformAssertions
     * @dataProvider provideValidConfigData()
     */
    public function testValidInput(string $invalidFilePath): void
    {
        $this->bootFromConfigFiles([$invalidFilePath]);

        $phpVersionProvider = $this->getService(PhpVersionProvider::class);
        $phpVersionProvider->provide();
    }

    /**
     * @return Iterator<string[]>
     */
    public function provideValidConfigData(): Iterator
    {
        yield [__DIR__ . '/config/valid_explicit_value.php'];
        yield [__DIR__ . '/config/valid_minus_value.php'];
    }

    /**
     * @dataProvider provideInvalidConfigData()
     */
    public function testInvalidInput(string $invalidFilePath): void
    {
        $this->expectException(InvalidConfigurationException::class);

        $this->bootFromConfigFiles([$invalidFilePath]);

        $phpVersionProvider = $this->getService(PhpVersionProvider::class);
        $phpVersionProvider->provide();
    }

    /**
     * @return Iterator<string[]>
     */
    public function provideInvalidConfigData(): Iterator
    {
        yield [__DIR__ . '/config/invalid_input.php'];
        yield [__DIR__ . '/config/invalid_string_input.php'];
        yield [__DIR__ . '/config/invalid_number_input.php'];
        yield [__DIR__ . '/config/invalid_php_4_number.php'];
    }
}
