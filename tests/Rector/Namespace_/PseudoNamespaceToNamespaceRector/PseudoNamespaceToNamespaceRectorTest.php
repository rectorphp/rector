<?php declare(strict_types=1);

namespace Rector\Tests\Rector\Namespace_\PseudoNamespaceToNamespaceRector;

use PHPUnit_Framework_MockObject_MockObject;
use Rector\Rector\Namespace_\PseudoNamespaceToNamespaceRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class PseudoNamespaceToNamespaceRectorTest extends AbstractRectorTestCase
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
        return PseudoNamespaceToNamespaceRector::class;
    }

    /**
     * @return mixed[]
     */
    protected function getRectorConfiguration(): array
    {
        return [
            'PHPUnit_' => [PHPUnit_Framework_MockObject_MockObject::class],
            'ChangeMe_' => ['KeepMe_'],
        ];
    }
}
