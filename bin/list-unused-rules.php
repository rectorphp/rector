<?php

declare (strict_types=1);
namespace RectorPrefix202510;

use RectorPrefix202510\Nette\Loaders\RobotLoader;
use Rector\Bridge\SetRectorsResolver;
use RectorPrefix202510\Symfony\Component\Console\Input\ArrayInput;
use RectorPrefix202510\Symfony\Component\Console\Output\ConsoleOutput;
use RectorPrefix202510\Symfony\Component\Console\Style\SymfonyStyle;
use RectorPrefix202510\Symfony\Component\Finder\Finder;
use RectorPrefix202510\Symfony\Component\Finder\SplFileInfo;
use RectorPrefix202510\Webmozart\Assert\Assert;
require __DIR__ . '/../vendor/autoload.php';
// 1. find all rector rules in here and in all vendor/rector dirs
$rectorClassFinder = new RectorClassFinder();
$rectorClasses = $rectorClassFinder->find([__DIR__ . '/../rules', __DIR__ . '/../vendor/rector/rector-doctrine', __DIR__ . '/../vendor/rector/rector-phpunit', __DIR__ . '/../vendor/rector/rector-symfony', __DIR__ . '/../vendor/rector/rector-downgrade-php']);
$symfonyStyle = new SymfonyStyle(new ArrayInput([]), new ConsoleOutput());
$symfonyStyle->writeln(\sprintf('<fg=green>Found Rector %d rules</>', \count($rectorClasses)));
$rectorSeFinder = new RectorSetFinder();
$rectorSetFiles = $rectorSeFinder->find([__DIR__ . '/../config/set', __DIR__ . '/../vendor/rector/rector-symfony/config/sets', __DIR__ . '/../vendor/rector/rector-doctrine/config/sets', __DIR__ . '/../vendor/rector/rector-phpunit/config/sets', __DIR__ . '/../vendor/rector/rector-downgrade-php/config/set']);
$symfonyStyle->writeln(\sprintf('<fg=green>Found %d sets</>', \count($rectorSetFiles)));
$usedRectorClassResolver = new UsedRectorClassResolver();
$usedRectorRules = $usedRectorClassResolver->resolve($rectorSetFiles);
$symfonyStyle->newLine();
$symfonyStyle->writeln(\sprintf('<fg=yellow>Found %d used Rector rules in sets</>', \count($usedRectorRules)));
$unusedRectorRules = \array_diff($rectorClasses, $usedRectorRules);
$symfonyStyle->writeln(\sprintf('<fg=yellow;options=bold>Found %d Rector rules not in any set</>', \count($unusedRectorRules)));
$symfonyStyle->newLine();
$symfonyStyle->listing($unusedRectorRules);
final class RectorClassFinder
{
    /**
     * @param string[] $dirs
     * @return string[]
     */
    public function find(array $dirs): array
    {
        $robotLoader = new RobotLoader();
        $robotLoader->acceptFiles = ['*Rector.php'];
        $robotLoader->addDirectory(...$dirs);
        $robotLoader->setTempDirectory(\sys_get_temp_dir() . '/rector-rules');
        $robotLoader->refresh();
        return \array_keys($robotLoader->getIndexedClasses());
    }
}
\class_alias('RectorClassFinder', 'RectorClassFinder', \false);
final class RectorSetFinder
{
    /**
     * @param string[] $configDirs
     * @return string[]
     */
    public function find(array $configDirs): array
    {
        Assert::allString($configDirs);
        Assert::allDirectory($configDirs);
        // find set files
        $finder = (new Finder())->in($configDirs)->files()->name('*.php');
        /** @var SplFileInfo[] $setFileInfos */
        $setFileInfos = \iterator_to_array($finder->getIterator());
        $setFiles = [];
        foreach ($setFileInfos as $setFileInfo) {
            $setFiles[] = $setFileInfo->getRealPath();
        }
        return $setFiles;
    }
}
\class_alias('RectorSetFinder', 'RectorSetFinder', \false);
final class UsedRectorClassResolver
{
    /**
     * @param string[] $rectorSetFiles
     * @return string[]
     */
    public function resolve(array $rectorSetFiles): array
    {
        $setRectorsResolver = new SetRectorsResolver();
        $rulesConfiguration = $setRectorsResolver->resolveFromFilePathsIncludingConfiguration($rectorSetFiles);
        $usedRectorRules = [];
        foreach ($rulesConfiguration as $ruleConfiguration) {
            $usedRectorRules[] = \is_string($ruleConfiguration) ? $ruleConfiguration : \array_keys($ruleConfiguration)[0];
        }
        \sort($usedRectorRules);
        return \array_unique($usedRectorRules);
    }
}
\class_alias('UsedRectorClassResolver', 'UsedRectorClassResolver', \false);
