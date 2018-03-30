<?php declare(strict_types=1);

namespace Rector\PharBuilder\Compiler;

use FilesystemIterator;
use Phar;
use Rector\PharBuilder\Exception\BinFileNotFoundException;
use Rector\PharBuilder\Exception\BuildDirNotCreatedException;
use Rector\PharBuilder\Filesystem\PathNormalizer;
use Seld\PharUtils\Timestamps;
use Symfony\Component\Console\Style\SymfonyStyle;

final class Compiler
{
    /**
     * @var string
     */
    private $pharName;

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
     * @var string
     */
    private $buildPharDirectory;

    /**
     * @var string
     */
    private $pharBaseName;

    /**
     * @var string
     */
    private $pharDirName;

    public function __construct(
        string $pharName,
        string $binFileName,
        string $buildDirectory,
        SymfonyStyle $symfonyStyle,
        PathNormalizer $pathNormalizer
    ) {
        $this->pharName = $pharName;
        $this->pharBaseName = basename($pharName);
        $this->pharDirName = dirname($pharName);
        $this->binFileName = $binFileName;
        $this->symfonyStyle = $symfonyStyle;
        $this->pathNormalizer = $pathNormalizer;
        $this->buildPharDirectory = realpath($buildDirectory);
    }

    public function compile(): void
    {
        $this->symfonyStyle->note(sprintf('Starting PHAR build in "%s" directory', $this->buildPharDirectory));

        $this->ensureBuildDirExists();

        // flags: KEY_AS_PATHNAME - use relative paths from Finder keys
        $phar = new Phar($this->pharName, FilesystemIterator::KEY_AS_PATHNAME, $this->pharBaseName);
        $phar->setSignatureAlgorithm(Phar::SHA1);
        $phar->startBuffering();

        $this->symfonyStyle->note(sprintf('Adding files from directory %s', $this->buildPharDirectory));

        $phar->buildFromDirectory($this->buildPharDirectory);

        $this->symfonyStyle->newLine(2);
        $this->symfonyStyle->note('Adding bin');
        $this->addRectorBin($phar, $this->buildPharDirectory);

        $this->symfonyStyle->note('Setting stub');
        $phar->setStub($this->getStub());
        $phar->stopBuffering();

        $timestamps = new Timestamps($this->pharName);
        $timestamps->save($this->pharName, Phar::SHA1);

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

        return sprintf($stubTemplate, $this->pharBaseName, $this->pharName, $this->binFileName);
    }

    private function removeShebang(string $content): string
    {
        return preg_replace('~^#!/usr/bin/env php\s*~', '', $content);
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

    private function ensureBuildDirExists(): void
    {
        if (! is_dir($this->pharDirName) && ! mkdir($this->pharDirName, 0777, true) && ! is_dir($this->pharDirName)) {
            throw new BuildDirNotCreatedException(
                sprintf('Directory "%s" was not created', $this->pharDirName)
            );
        }
    }
}
