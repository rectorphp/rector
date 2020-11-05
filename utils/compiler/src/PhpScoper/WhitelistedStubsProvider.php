<?php

declare(strict_types=1);

namespace Rector\Compiler\PhpScoper;

use SplFileInfo;
use Symfony\Component\Finder\Finder;

final class WhitelistedStubsProvider
{
    /**
     * @return string[]
     */
    public function provide(): array
    {
        $stubs = [
            // @see https://github.com/rectorphp/rector/issues/2852#issuecomment-586315588
            'vendor/hoa/consistency/Prelude.php',
            'vendor/symfony/deprecation-contracts/function.php',
        ];

        // mirrors https://github.com/phpstan/phpstan-src/commit/04f777bc4445725d17dac65c989400485454b145
        $stubsDirectory = __DIR__ . '/../../../../vendor/jetbrains/phpstorm-stubs';
        if (file_exists($stubsDirectory)) {
            $stubFinder = Finder::create()
                ->files()
                ->name('*.php')
                ->in($stubsDirectory)
                ->notName('#PhpStormStubsMap\.php$#');

            foreach ($stubFinder->getIterator() as $fileInfo) {
                /** @var SplFileInfo $fileInfo */
                $stubs[] = $fileInfo->getPathName();
            }
        }

        return $stubs;
    }
}
