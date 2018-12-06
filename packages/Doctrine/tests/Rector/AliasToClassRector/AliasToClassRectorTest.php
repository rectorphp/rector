<?php declare(strict_types=1);

namespace Rector\Doctrine\Tests\Rector\AliasToClassRector;

use Rector\Doctrine\Rector\AliasToClassRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class AliasToClassRectorTest extends AbstractRectorTestCase
{
    public function test(): void
    {
        $this->doTestFiles([__DIR__ . '/Fixture/fixture.php.inc']);
    }

    protected function getRectorClass(): string
    {
        return AliasToClassRector::class;
    }

    /**
     * @return mixed[]
     */
    protected function getRectorConfiguration(): array
    {
        return ['App' => 'App\Entity'];
    }
}
