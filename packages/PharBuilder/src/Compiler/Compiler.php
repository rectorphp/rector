<?php declare(strict_types=1);

namespace Rector\PharBuilder\Compiler;

use FilesystemIterator;
use Phar;
use Rector\PharBuilder\Exception\BinFileNotFoundException;
use Rector\PharBuilder\Filesystem\PathNormalizer;
use Rector\PharBuilder\Filesystem\PharFilesFinder;
use Rector\PharBuilder\FinderToPharAdder;
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

    /**
     * @var PathNormalizer
     */
    private $pathNormalizer;

    /**
     * @var FinderToPharAdder
     */
    private $finderToPharAdder;
    /**
     * @var string
     */
    private $buildDirectory;

    public function __construct(
        string $pharName,
        string $binFileName,
        string $buildDirectory,
        PharFilesFinder $pharFilesFinder,
        SymfonyStyle $symfonyStyle,
        PathNormalizer $pathNormalizer,
        FinderToPharAdder $finderToPharAdder
    ) {
        $this->pharName = $pharName;
        $this->pharFilesFinder = $pharFilesFinder;
        $this->binFileName = $binFileName;
        $this->symfonyStyle = $symfonyStyle;
        $this->pathNormalizer = $pathNormalizer;
        $this->finderToPharAdder = $finderToPharAdder;
        $this->buildDirectory = realpath($buildDirectory);
    }

    public function compile(): void
    {
        $this->symfonyStyle->note(sprintf('Starting PHAR build in "%s" directory', $this->buildDirectory));

        // flags: KEY_AS_PATHNAME - use relative paths from Finder keys
        $phar = new Phar($this->pharName, FilesystemIterator::KEY_AS_PATHNAME, $this->pharName);
        $phar->setSignatureAlgorithm(Phar::SHA1);
        $phar->startBuffering();

        // use only dev deps + rebuild dump autoload
//        $this->symfonyStyle->note('Removing dev packages from composer');
//        $process = new Process('composer update --no-dev', $buildDirectory);
//        $process->run();

        // dump autoload
//        $this->symfonyStyle->note('Dumping new composer autoload');
//        $process = new Process('composer dump-autoload --optimize', $buildDirectory);
//        $process->run();

        $finder = $this->pharFilesFinder->createForDirectory($this->buildDirectory);

        $fileCount = $this->getFileCountFromFinder($finder);
        $this->symfonyStyle->note(sprintf('Adding %d files', $fileCount));
        $this->symfonyStyle->progressStart($fileCount);

        $this->finderToPharAdder->addFinderToPhar($finder, $phar);

        $this->symfonyStyle->newLine(2);
        $this->symfonyStyle->note('Adding bin');
        $this->addRectorBin($phar, $this->buildDirectory);

        $this->symfonyStyle->note('Setting stub');
        $phar->setStub($this->getStub());
        $phar->stopBuffering();

        $timestamps = new Timestamps($this->pharName);
        $timestamps->save($this->pharName, Phar::SHA1);

        // return dev deps
//        $this->symfonyStyle->note('Returning dev packages to composer');
//        $process = new Process('composer update', $buildDirectory);
//        $process->run();

        $this->symfonyStyle->success(sprintf('Phar file "%s" build successful!', $this->pharName));
    }

    private function addRectorBin(Phar $phar, string $buildDirectory): void
    {
        $binFilePath = $buildDirectory . DIRECTORY_SEPARATOR . $this->binFileName;
        $this->ensureBinFileExists($binFilePath);

        $content = file_get_contents($binFilePath);
        $content = $this->removeShebang($content);

        // replace absolute paths by phar:// paths
        $content = $this->pathNormalizer->normalizeAbsoluteToPharInContent($content);

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

    private function getFileCountFromFinder(Finder $finder): int
    {
        return count(iterator_to_array($finder->getIterator()));
    }

    private function ensureBinFileExists(string $binFilePath): void
    {
        if (file_exists($binFilePath)) {
            return;
        }

        throw new BinFileNotFoundException(sprintf(
            'Bin file not found in "%s". Have you set it up in config.yml file?',
            $binFilePath
        ));
    }
}
