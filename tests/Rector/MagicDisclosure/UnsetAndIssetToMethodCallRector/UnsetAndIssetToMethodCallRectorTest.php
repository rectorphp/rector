<?php declare(strict_types=1);

namespace Rector\Tests\Rector\MagicDisclosure\UnsetAndIssetToMethodCallRector;

use Nette\DI\Container;
use Rector\Rector\MagicDisclosure\UnsetAndIssetToMethodCallRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class UnsetAndIssetToMethodCallRectorTest extends AbstractRectorTestCase
{
    public function test(): void
    {
        $this->doTestFiles([__DIR__ . '/Fixture/fixture.php.inc']);
    }

    protected function getRectorClass(): string
    {
        return UnsetAndIssetToMethodCallRector::class;
    }

    /**
     * @return mixed[]
     */
    protected function getRectorConfiguration(): array
    {
        return [Container::class => [
            'isset' => 'hasService',
            'unset' => 'removeService',
        ]];
    }
}
