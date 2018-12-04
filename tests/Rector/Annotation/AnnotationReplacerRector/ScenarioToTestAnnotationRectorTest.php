<?php declare(strict_types=1);

namespace Rector\Tests\Rector\Annotation\AnnotationReplacerRector;

use PHPUnit\Framework\TestCase;
use Rector\Rector\Annotation\AnnotationReplacerRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class ScenarioToTestAnnotationRectorTest extends AbstractRectorTestCase
{
    public function test(): void
    {
        $this->doTestFiles([__DIR__ . '/Wrong/wrong.php.inc']);
    }

    protected function getRectorClass(): string
    {
        return AnnotationReplacerRector::class;
    }

    /**
     * @return mixed[]
     */
    protected function getRectorConfiguration(): array
    {
        return [TestCase::class => ['scenario' => 'test']];
    }
}
