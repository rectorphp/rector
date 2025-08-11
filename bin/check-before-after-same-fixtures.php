<?php

declare (strict_types=1);
namespace RectorPrefix202508;

use RectorPrefix202508\Nette\Utils\Strings;
require __DIR__ . '/../vendor/autoload.php';
use RectorPrefix202508\Symfony\Component\Console\Command\Command;
use RectorPrefix202508\Symfony\Component\Console\Input\ArgvInput;
use RectorPrefix202508\Symfony\Component\Console\Output\ConsoleOutput;
use RectorPrefix202508\Symfony\Component\Console\Style\SymfonyStyle;
use RectorPrefix202508\Symfony\Component\Finder\Finder;
use RectorPrefix202508\Symfony\Component\Finder\SplFileInfo;
use RectorPrefix202508\Webmozart\Assert\Assert;
final class SameBeforeAfterFixtureDetector
{
    /**
     * @readonly
     */
    private SymfonyStyle $symfonyStyle;
    public function __construct()
    {
        $this->symfonyStyle = new SymfonyStyle(new ArgvInput(), new ConsoleOutput());
    }
    /**
     * @param string[] $testDirectories
     * @return Command::SUCCESS|Command::FAILURE
     */
    public function run(array $testDirectories) : int
    {
        $fixtureFiles = $this->findFixtureFiles($testDirectories);
        $invalidFixturePaths = [];
        foreach ($fixtureFiles as $fixtureFile) {
            if (!$this->hasFileSameBeforeAndAfterPart($fixtureFile)) {
                continue;
            }
            $invalidFixturePaths[] = \substr($fixtureFile->getRealPath(), \strlen(\getcwd()) + 1);
        }
        if ($invalidFixturePaths === []) {
            $this->symfonyStyle->success('All fixtures are valid');
            return Command::SUCCESS;
        }
        $this->symfonyStyle->error('The following fixtures have the same before and after content. Remove the part after "-----" to fix them');
        $this->symfonyStyle->listing($invalidFixturePaths);
        return Command::FAILURE;
    }
    /**
     * @param string[] $directories
     * @return SplFileInfo[]
     */
    private function findFixtureFiles(array $directories) : array
    {
        Assert::allDirectory($directories);
        $finder = (new Finder())->files()->in($directories)->name('*.php.inc')->sortByName();
        return \iterator_to_array($finder->getIterator());
    }
    private function hasFileSameBeforeAndAfterPart(SplFileInfo $fixtureFile) : bool
    {
        $parts = Strings::split($fixtureFile->getContents(), '#^\\s*-----\\s*$#m');
        if (\count($parts) !== 2) {
            return \false;
        }
        return \trim((string) $parts[0]) === \trim((string) $parts[1]);
    }
}
\class_alias('SameBeforeAfterFixtureDetector', 'SameBeforeAfterFixtureDetector', \false);
$sameBeforeAfterFixtureDetector = new SameBeforeAfterFixtureDetector();
exit($sameBeforeAfterFixtureDetector->run([__DIR__ . '/../tests', __DIR__ . '/../rules-tests']));
