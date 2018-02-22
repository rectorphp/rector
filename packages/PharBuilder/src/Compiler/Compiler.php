<?php declare(strict_types=1);

namespace Rector\PharBuilder\Compiler;

use FilesystemIterator;
use Phar;
use Rector\PharBuilder\Filesystem\PharFilesFinder;
use Seld\PharUtils\Timestamps;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Process\Process;

final class Compiler
{
    /**
     * @var string
     */
    private $pharName;

    /**
     * @var PharFilesFinder
     */
    private $pharFilesFinder;

    /**
     * @var SymfonyStyle
     */
    private $symfonyStyle;

    /**
     * @var string
     */
    private $binFileName;

    public function __construct(
        string $pharName,
        string $binFileName,
        PharFilesFinder $pharFilesFinder,
        SymfonyStyle $symfonyStyle
    ) {
        $this->pharName = $pharName;
        $this->pharFilesFinder = $pharFilesFinder;
        $this->binFileName = $binFileName;
        $this->symfonyStyle = $symfonyStyle;
    }

    public function compile(string $buildDir): void
    {
        $this->symfonyStyle->note('Starting PHAR build');

        // flags: KEY_AS_PATHNAME - use relative paths from Finder keys
        $phar = new Phar($this->pharName, FilesystemIterator::KEY_AS_PATHNAME, $this->pharName);
        $phar->setSignatureAlgorithm(Phar::SHA1);
        $phar->startBuffering();

        // use only dev deps + rebuild dump autoload
        $this->symfonyStyle->note('Removing dev packages from composer');
        $process = new Process('composer update --no-dev', $buildDir);
        $process->run();

        // dump autoload
        $this->symfonyStyle->note('Dumping new composer autoload');
        $process = new Process('composer dump-autoload --optimize', $buildDir);
        $process->run();

        $finder = $this->pharFilesFinder->createForDirectory($buildDir);

        $this->symfonyStyle->note('Adding files');
        $fileCount = count(iterator_to_array($finder->getIterator()));
        $this->symfonyStyle->progressStart($fileCount);

        $this->addFinderFilesToPhar($finder, $phar);

        $this->symfonyStyle->newLine(2);
        $this->symfonyStyle->note('Adding bin');
        $this->addRectorBin($phar);

        $this->symfonyStyle->note('Setting stub');
        $phar->setStub($this->getStub());
        $phar->stopBuffering();

        $timestamps = new Timestamps($this->pharName);
        $timestamps->save($this->pharName, Phar::SHA1);

        // return dev deps
        $this->symfonyStyle->note('Returning dev packages to composer');
        $process = new Process('composer update', $buildDir);
        $process->run();

        $this->symfonyStyle->success(sprintf('Phar file "%s" build successful!', $this->pharName));
    }

    private function addRectorBin(Phar $phar): void
    {
        $content = file_get_contents(__DIR__ . '/../../../../' . $this->binFileName);
        $content = $this->removeShebang($content);
        // replace relative paths by phar paths
        $content = preg_replace(
            "~__DIR__\\s*\\.\\s*'\\/\\.\\.\\/~",
            sprintf("'phar://%s/", $this->pharName),
            $content
        );

        $phar->addFromString($this->binFileName, $content);
    }

    private function getStub(): string
    {
        $stubTemplate = <<<'EOF'
#!/usr/bin/env php
<?php
Phar::mapPhar('%s');
require 'phar://%s/%s';
__HALT_COMPILER();
EOF;

        return sprintf($stubTemplate, $this->pharName, $this->pharName, $this->binFileName);
    }

    private function removeShebang(string $content): string
    {
        return preg_replace('~^#!/usr/bin/env php\s*~', '', $content);
    }

    private function addFinderFilesToPhar(Finder $finder, Phar $phar): void
    {
        foreach ($finder as $relativeFilePath => $splFileInfo) {
            $phar->addFile($relativeFilePath);
            $this->symfonyStyle->progressAdvance();
        }
    }
}
