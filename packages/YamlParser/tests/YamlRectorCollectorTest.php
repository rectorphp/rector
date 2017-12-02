<?php declare(strict_types=1);

namespace Rector\YamlParser\Tests;

use Rector\Tests\AbstractContainerAwareTestCase;
use Rector\YamlParser\YamlRectorCollector;

final class YamlRectorCollectorTest extends AbstractContainerAwareTestCase
{
    /**
     * @var YamlRectorCollector
     */
    private $yamlRectorCollector;

    protected function setUp(): void
    {
        $this->yamlRectorCollector = $this->container->get(YamlRectorCollector::class);
    }

    public function testSymfonyServiceRename(): void
    {
        $file = __DIR__ . '/YamlRectorCollectorSource/some_services.yml';

        $this->assertStringEqualsFile(
            __DIR__ . '/YamlRectorCollectorSource/expected.some_services.yml',
            $this->yamlRectorCollector->processFile($file)
        );
    }
}
