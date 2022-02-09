<?php

declare (strict_types=1);
namespace Rector\VersionBonding\Application;

use RectorPrefix20220209\Nette\Utils\Strings;
use PHPStan\Php\PhpVersion;
use Rector\Core\Contract\Rector\RectorInterface;
use Rector\Core\Php\PhpVersionProvider;
use Rector\VersionBonding\Contract\MinPhpVersionInterface;
use RectorPrefix20220209\Symfony\Component\Console\Style\SymfonyStyle;
final class MissedRectorDueVersionChecker
{
    /**
     * @readonly
     * @var \Rector\Core\Php\PhpVersionProvider
     */
    private $phpVersionProvider;
    /**
     * @readonly
     * @var \Symfony\Component\Console\Style\SymfonyStyle
     */
    private $symfonyStyle;
    public function __construct(\Rector\Core\Php\PhpVersionProvider $phpVersionProvider, \RectorPrefix20220209\Symfony\Component\Console\Style\SymfonyStyle $symfonyStyle)
    {
        $this->phpVersionProvider = $phpVersionProvider;
        $this->symfonyStyle = $symfonyStyle;
    }
    /**
     * @param RectorInterface[] $rectors
     */
    public function check(array $rectors) : void
    {
        $minProjectPhpVersion = $this->phpVersionProvider->provide();
        $missedRectors = $this->resolveMissedRectors($rectors, $minProjectPhpVersion);
        if ($missedRectors === []) {
            return;
        }
        $this->reportWarningMessage($minProjectPhpVersion, $missedRectors);
        $this->reportMissedRectors($missedRectors);
        $solutionMessage = \sprintf('Do you want to run them? Make "require" > "php" in `composer.json` higher,%sor add "Option::PHP_VERSION_FEATURES" parameter to your `rector.php`.', \PHP_EOL);
        $this->symfonyStyle->note($solutionMessage);
    }
    /**
     * @param RectorInterface[] $rectors
     * @return MinPhpVersionInterface[]
     */
    private function resolveMissedRectors(array $rectors, int $minProjectPhpVersion) : array
    {
        $missedRectors = [];
        foreach ($rectors as $rector) {
            if (!$rector instanceof \Rector\VersionBonding\Contract\MinPhpVersionInterface) {
                continue;
            }
            // the conditions are met → skip it
            if ($rector->provideMinPhpVersion() <= $minProjectPhpVersion) {
                continue;
            }
            $missedRectors[] = $rector;
        }
        return $missedRectors;
    }
    /**
     * @param MinPhpVersionInterface[] $minPhpVersions
     */
    private function reportMissedRectors(array $minPhpVersions) : void
    {
        if (!$this->symfonyStyle->isVerbose()) {
            return;
        }
        foreach ($minPhpVersions as $minPhpVersion) {
            $phpVersion = new \PHPStan\Php\PhpVersion($minPhpVersion->provideMinPhpVersion());
            $shortRectorClass = \RectorPrefix20220209\Nette\Utils\Strings::after(\get_class($minPhpVersion), '\\', -1);
            $rectorMessage = \sprintf(' * [%s] %s', $phpVersion->getVersionString(), $shortRectorClass);
            $this->symfonyStyle->writeln($rectorMessage);
        }
    }
    /**
     * @param MinPhpVersionInterface[] $missedRectors
     */
    private function reportWarningMessage(int $minProjectPhpVersion, array $missedRectors) : void
    {
        $phpVersion = new \PHPStan\Php\PhpVersion($minProjectPhpVersion);
        $warningMessage = \sprintf('Your project requires min PHP version "%s". %s%d Rector rules defined in your configuration require higher PHP version and will not run,%sto avoid breaking your codebase, use -vvv for detailed info.', $phpVersion->getVersionString(), \PHP_EOL . \PHP_EOL, \count($missedRectors), \PHP_EOL);
        $this->symfonyStyle->warning($warningMessage);
    }
}
