<?php

declare (strict_types=1);
namespace Rector\Console\Command;

use RectorPrefix202607\Composer\Semver\Semver;
use Rector\Composer\InstalledPackageResolver;
use Rector\Contract\Rector\RectorInterface;
use Rector\VersionBonding\Contract\ComposerPackageConstraintInterface;
use RectorPrefix202607\Symfony\Component\Console\Command\Command;
use RectorPrefix202607\Symfony\Component\Console\Input\InputInterface;
use RectorPrefix202607\Symfony\Component\Console\Output\OutputInterface;
use RectorPrefix202607\Symfony\Component\Console\Style\SymfonyStyle;
/**
 * @see \Rector\Tests\Console\Command\ComposerBasedCommandTest
 */
final class ComposerBasedCommand extends Command
{
    /**
     * @readonly
     */
    private SymfonyStyle $symfonyStyle;
    /**
     * @readonly
     */
    private InstalledPackageResolver $installedPackageResolver;
    /**
     * @var RectorInterface[]
     * @readonly
     */
    private array $rectors;
    /**
     * @param RectorInterface[] $rectors
     */
    public function __construct(SymfonyStyle $symfonyStyle, InstalledPackageResolver $installedPackageResolver, array $rectors)
    {
        $this->symfonyStyle = $symfonyStyle;
        $this->installedPackageResolver = $installedPackageResolver;
        $this->rectors = $rectors;
        parent::__construct();
    }
    protected function configure(): void
    {
        $this->setName('composer-based');
        $this->setDescription('Show loaded rules that are triggered by an installed composer package version');
    }
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $tableRows = $this->createTableRows();
        if ($tableRows === []) {
            $this->symfonyStyle->warning('No composer package bound rule is loaded');
            return Command::SUCCESS;
        }
        $this->symfonyStyle->title('Composer package bound rules');
        $this->symfonyStyle->table(['Rule', 'Package', 'Requires', 'Installed', 'Active'], $tableRows);
        $activeCount = count(array_filter($tableRows, static fn(array $tableRow): bool => $tableRow[4] === 'yes'));
        $this->symfonyStyle->note(sprintf('%d of %d composer package bound rules are active', $activeCount, count($tableRows)));
        return Command::SUCCESS;
    }
    /**
     * @return array<array{string, string, string, string, string}>
     */
    private function createTableRows(): array
    {
        $tableRows = [];
        foreach ($this->rectors as $rector) {
            if (!$rector instanceof ComposerPackageConstraintInterface) {
                continue;
            }
            $composerPackageConstraint = $rector->provideComposerPackageConstraint();
            $packageName = $composerPackageConstraint->getPackageName();
            $constraint = $composerPackageConstraint->getConstraint();
            $installedVersion = $this->installedPackageResolver->resolvePackageVersion($packageName);
            $isActive = $installedVersion !== null && Semver::satisfies($installedVersion, $constraint);
            $tableRows[] = [get_class($rector), $packageName, $constraint, $installedVersion ?? '-', $isActive ? 'yes' : 'no'];
        }
        // sort by package name first, then by rule class
        usort($tableRows, static fn(array $firstTableRow, array $secondTableRow): int => [$firstTableRow[1], $firstTableRow[0]] <=> [$secondTableRow[1], $secondTableRow[0]]);
        return $tableRows;
    }
}
