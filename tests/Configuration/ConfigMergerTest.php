<?php declare(strict_types=1);

namespace Rector\Tests\Configuration;

use PHPUnit\Framework\TestCase;
use Rector\Configuration\ConfigMerger;
use Symfony\Component\Yaml\Yaml;

final class ConfigMergerTest extends TestCase
{
    /**
     * @var ConfigMerger
     */
    private $configMerger;

    protected function setUp(): void
    {
        $this->configMerger = new ConfigMerger();
    }

    public function test(): void
    {
        $result = $this->configMerger->mergeConfigs([
            Yaml::parseFile(__DIR__ . '/ConfigMergerSource/config1.yml'),
            Yaml::parseFile(__DIR__ . '/ConfigMergerSource/config2.yml'),
        ]);

        $this->assertSame(Yaml::parseFile(__DIR__ . '/ConfigMergerSource/expected-config1-2.yml'), $result);
    }
}
