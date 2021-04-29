<?php

// inspired at https://github.com/phpstan/phpstan-src/commit/87897c2a4980d68efa1c46049ac2eefe767ec946#diff-e897e523125a694bd8ea69bf83374c206803c98720c46d7401b7a7cf53915a26

declare(strict_types=1);

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

    $preloadScript = $buildDirectory . '/preload.php';
    $template = <<<'php'
<?php

declare(strict_types = 1);

%s
php;
    $root = realpath(__DIR__ . '/..');
    if ($root === false) {
        return;
    }
    $output = '';

    $finder = (new Finder())
        ->files()
        ->name('*.php')
        ->in($vendorDir . '/nikic/php-parser/lib/PhpParser');

    foreach ($finder->getIterator() as $fileInfo) {
        $realPath = $fileInfo->getRealPath();
        if ($realPath === false) {
            continue;
        }

        $path = substr($realPath, strlen($root));
        $output .= 'require_once __DIR__ . ' . var_export($path, true) . ';' . PHP_EOL;
    }

    file_put_contents($preloadScript, sprintf($template, $output));
}
