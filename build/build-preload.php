<?php

// inspired at https://github.com/phpstan/phpstan-src/commit/87897c2a4980d68efa1c46049ac2eefe767ec946#diff-e897e523125a694bd8ea69bf83374c206803c98720c46d7401b7a7cf53915a26

declare(strict_types=1);

use Nette\Utils\Strings;
use Symfony\Component\Finder\Finder;

require __DIR__ . '/../vendor/autoload.php';

$buildDirectory = $argv[1];

buildPreloadScript($buildDirectory);

function buildPreloadScript(string $buildDirectory): void
{
    $vendorDir = $buildDirectory . '/vendor';
    if (!is_dir($vendorDir . '/nikic/php-parser/lib/PhpParser')) {
        return;
    }

    $preloadFileContent = <<<'php'
<?php

declare(strict_types=1);


php;

    $finder = (new Finder())
        ->files()
        ->name('*.php')
        ->in($vendorDir . '/nikic/php-parser/lib/PhpParser')
        ->notPath('#\/tests\/#')
        ->notPath('#\/config\/#')
        ->notPath('#\/set\/#')
        ->in($vendorDir . '/symplify/symfony-php-config');

    $fileInfos = iterator_to_array($finder->getIterator());
    $fileInfos[] = new SplFileInfo(__DIR__ . '/../vendor/symfony/dependency-injection/Loader/Configurator/ContainerConfigurator.php');

    foreach ($fileInfos as $fileInfo) {
        $realPath = $fileInfo->getRealPath();
        if ($realPath === false) {
            continue;
        }

        $filePath = '/vendor/' . Strings::after($realPath, 'vendor/');
        $preloadFileContent .= "require_once __DIR__ . '" . $filePath . "';" . PHP_EOL;
    }

    file_put_contents($buildDirectory . '/preload.php', $preloadFileContent);
}
