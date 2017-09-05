<?php declare(strict_types=1);

namespace Rector\TriggerExtractor\Tests;

use Rector\Tests\AbstractContainerAwareTestCase;
use Rector\TriggerExtractor\TriggerExtractor;

final class TriggerExtractorTest extends AbstractContainerAwareTestCase
{
    /**
     * @var TriggerExtractor
     */
    private $triggerExtractor;

    protected function setUp(): void
    {
        $this->triggerExtractor = $this->container->get(TriggerExtractor::class);
    }

    public function test(): void
    {
        $foundDeprecations = $this->triggerExtractor->scanDirectories([__DIR__ . '/TriggerExtractorSource']);
        $this->assertCount(2, $foundDeprecations);
    }
}
