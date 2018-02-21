<?php declare(strict_types=1);

namespace Rector\PharBuilder\Compiler;

use FilesystemIterator;
use Phar;
use Rector\PharBuilder\Filesystem\PharFilesFinder;
use Seld\PharUtils\Timestamps;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * 1st step: make hardcoded work
 * 2nd step: make abstract work - use Configuration class to provide all the stuffs
 */
final class Compiler
{
    /**
     * @var PharFilesFinder
     */
    private $pharFilesFinder;

    /**
     * @var SymfonyStyle
     */
    private $symfonyStyle;

    public function __construct(PharFilesFinder $pharFilesFinder, SymfonyStyle $symfonyStyle)
    {
        $this->pharFilesFinder = $pharFilesFinder;
        $this->symfonyStyle = $symfonyStyle;
    }

    public function compile(string $buildDir): void
    {
        $this->symfonyStyle->note('Starting PHAR build');

        // flags: KEY_AS_PATHNAME - use relative paths from Finder keys
        $phar = new Phar('rector.phar', FilesystemIterator::KEY_AS_PATHNAME, 'rector.phar');
        $phar->setSignatureAlgorithm(Phar::SHA1);
        $phar->startBuffering();

        $finder = $this->pharFilesFinder->createForDirectory($buildDir);

        $this->symfonyStyle->note('Adding files');
        $fileCount = count(iterator_to_array($finder->getIterator()));
        $this->symfonyStyle->progressStart($fileCount);

        foreach ($finder as $relativeFilePath => $splFileInfo) {
            $phar->addFile($relativeFilePath);
            $this->symfonyStyle->progressAdvance();
        }

        $this->symfonyStyle->note('Adding bin');
        $this->addRectorBin($phar);
        $phar->compress(Phar::GZ);

        $this->symfonyStyle->note('Setting stub');
        $phar->setStub($this->getStub());
        $phar->stopBuffering();

        $timestamps = new Timestamps('rector.phar');
        $timestamps->save('rector.phar', Phar::SHA1);

        $this->symfonyStyle->success(sprintf('Phar file "%s" build successful!', 'rector.phar'));
    }

    private function addRectorBin(Phar $phar): void
    {
        $content = file_get_contents(__DIR__ . '/../../../bin/rector');
        // remove shebang from bin, causes errors
        $content = preg_replace('~^#!/usr/bin/env php\s*~', '', $content);
        // replace relative paths by phar paths
        $content = preg_replace("~__DIR__\\s*\\.\\s*'\\/\\.\\.\\/~", "'phar://rector.phar/", $content);

        $phar->addFromString('bin/rector', $content);
    }

    private function getStub(): string
    {
        return <<<'EOF'
#!/usr/bin/env php
<?php
Phar::mapPhar('rector.phar');
require 'phar://rector.phar/bin/rector';
__HALT_COMPILER();
EOF;
    }
}
