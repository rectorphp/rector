<?php declare(strict_types=1);

namespace Rector\Tests\Rector\Namespace_\NamespaceReplacerRector;

use Rector\Rector\Namespace_\NamespaceReplacerRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class NamespaceReplacerRectorTest extends AbstractRectorTestCase
{
    public function test(): void
    {
        $this->doTestFiles([
            __DIR__ . '/Fixture/fixture.php.inc',
            __DIR__ . '/Fixture/fixture2.php.inc',
            __DIR__ . '/Fixture/fixture3.php.inc',
            __DIR__ . '/Fixture/fixture4.php.inc',
            __DIR__ . '/Fixture/fixture5.php.inc',
        ]);
    }

    protected function getRectorClass(): string
    {
        return NamespaceReplacerRector::class;
    }

    /**
     * @return mixed[]
     */
    protected function getRectorConfiguration(): array
    {
        return [
            'OldNamespace' => 'NewNamespace',
            'OldNamespaceWith\OldSplitNamespace' => 'NewNamespaceWith\NewSplitNamespace',
            'Old\Long\AnyNamespace' => 'Short\AnyNamespace',
            'PHPUnit_Framework_' => 'PHPUnit\Framework\\',
        ];
    }
}
