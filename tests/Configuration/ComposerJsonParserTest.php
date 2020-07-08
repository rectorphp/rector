<?php

declare(strict_types=1);

namespace Rector\Core\Tests\Configuration;

use Nette\Utils\Json;
use Rector\Core\Configuration\MinimalVersionChecker\ComposerJsonParser;
use Symplify\PackageBuilder\Tests\AbstractKernelTestCase;

final class ComposerJsonParserTest extends AbstractKernelTestCase
{
    /**
     * @dataProvider dataProvider
     */
    public function test($expectedVersion, string $version): void
    {
        $actualPhpVersion = $this->getComposerJsonPhpVersion($version);

        $this->assertSame($expectedVersion, $actualPhpVersion);
    }

    public function dataProvider()
    {
        return [
            ['7.2.0', '7.2.0'],
            ['7.2.0', '~7.2.0'],
            ['7.2', '7.2.*'],
            ['7', '7.*.*'],
            ['7.2.0', '~7.2.0'],
            ['7.2.0', '^7.2.0'],
            ['7.2.0', '>=7.2.0'],
        ];
    }

    private function getComposerJsonPhpVersion(string $version): string
    {
        $parser = new ComposerJsonParser(Json::encode([
            'require' =>
                [
                    'php' => $version,
                ],
        ]));
        return $parser->getPhpVersion();
    }
}
