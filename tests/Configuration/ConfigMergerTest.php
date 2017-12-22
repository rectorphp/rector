<?php declare(strict_types=1);

namespace Rector\Tests\Configuration;

use PHPUnit\Framework\TestCase;
use Rector\Configuration\ConfigMerger;

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
            ['SomeRector' => ['key' => 'value']]
        ]);

        $this->assertSame(['SomeRector' => ['key' => 'value']], $result);

        $result = $this->configMerger->mergeConfigs([
            ['SomeRector' => ['key' => ['value']]],
            ['SomeRector' => ['key' => ['another_value']]]
        ]);

        $this->assertSame(['SomeRector' => ['key' => 'value']], $result);

    }
}

