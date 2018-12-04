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
            __DIR__ . '/Wrong/wrong.php.inc',
            __DIR__ . '/Wrong/wrong2.php.inc',
            __DIR__ . '/Wrong/wrong3.php.inc',
            __DIR__ . '/Wrong/wrong4.php.inc',
            __DIR__ . '/Wrong/wrong5.php.inc',
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
