<?php declare(strict_types=1);

namespace Rector\PharBuilder\Compiler;

use FilesystemIterator;
use Phar;
use Rector\PharBuilder\Filesystem\PharFilesFinder;
use Seld\PharUtils\Timestamps;

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

    public function __construct(PharFilesFinder $pharFilesFinder)
    {
        $this->pharFilesFinder = $pharFilesFinder;
    }

    public function compile(string $buildDir): void
    {
        // flags: KEY_AS_PATHNAME - use relative paths from Finder keys
        $phar = new Phar('rector.phar', FilesystemIterator::KEY_AS_PATHNAME, 'rector.phar');
        $phar->setSignatureAlgorithm(Phar::SHA1);
        $phar->startBuffering();

        $finder = $this->pharFilesFinder->createFinderWithAllFiles($buildDir);
        $phar->buildFromIterator($finder->getIterator(), $buildDir);

        $this->addRectorBin($phar);
        $phar->compress(Phar::GZ);

        $phar->setStub($this->getStub());
        $phar->stopBuffering();

        $timestamps = new Timestamps('rector.phar');
        $timestamps->save('rector.phar', Phar::SHA1);
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
