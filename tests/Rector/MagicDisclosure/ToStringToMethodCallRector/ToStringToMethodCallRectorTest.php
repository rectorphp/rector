<?php declare(strict_types=1);

namespace Rector\Tests\Rector\MagicDisclosure\ToStringToMethodCallRector;

use Rector\Rector\MagicDisclosure\ToStringToMethodCallRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;
use Symfony\Component\Config\ConfigCache;

final class ToStringToMethodCallRectorTest extends AbstractRectorTestCase
{
    public function test(): void
    {
        $this->doTestFiles([__DIR__ . '/Fixture/fixture.php.inc']);
    }

    /**
     * @return mixed[]
     */
    protected function getRectorsWithConfiguration(): array
    {
        return [
            ToStringToMethodCallRector::class => [
                '$methodNamesByType' => [
                    ConfigCache::class => 'getPath',
                ],
            ],
        ];
    }
}
