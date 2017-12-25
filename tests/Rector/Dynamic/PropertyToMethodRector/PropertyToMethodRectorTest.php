<?php declare(strict_types=1);

namespace Rector\Tests\Rector\Dynamic\PropertyToMethodRector;

use Rector\Rector\Dynamic\PropertyToMethodRector;
use Rector\Testing\PHPUnit\AbstractConfigurableRectorTestCase;

final class PropertyToMethodRectorTest extends AbstractConfigurableRectorTestCase
{
    public function test(): void
    {
        $this->doTestFileMatchesExpectedContent(
            __DIR__ . '/wrong/wrong.php.inc',
            __DIR__ . '/correct/correct.php.inc'
        );
    }

    /**
     * @return string[]
     */
    protected function getRectorClasses(): array
    {
        return [PropertyToMethodRector::class];
    }

    protected function provideConfig(): string
    {
        return __DIR__ . '/config/rector.yml';
    }
}
