<?php declare(strict_types=1);

namespace Rector\Tests\Rector\MagicDisclosure\ToStringToMethodCallRector;

use Rector\Rector\MagicDisclosure\ToStringToMethodCallRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;
use Symfony\Component\Config\ConfigCache;

final class ToStringToMethodCallRectorTest extends AbstractRectorTestCase
{
    public function test(): void
    {
        $this->doTestFiles([__DIR__ . '/Wrong/wrong.php.inc']);
    }

    protected function getRectorClass(): string
    {
        return ToStringToMethodCallRector::class;
    }

    /**
     * @return mixed[]
     */
    protected function getRectorConfiguration(): array
    {
        return [ConfigCache::class => 'getPath'];
    }
}
