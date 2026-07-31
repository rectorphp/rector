<?php

declare (strict_types=1);
namespace Rector\Console\Command;

use RectorPrefix202607\Composer\Semver\Semver;
use RectorPrefix202607\Nette\Utils\Strings;
use Rector\Composer\InstalledPackageResolver;
use Rector\Configuration\Option;
use Rector\Configuration\Parameter\SimpleParameterProvider;
use Rector\Contract\Rector\RectorInterface;
use Rector\VersionBonding\Contract\ComposerPackageConstraintInterface;
use Rector\VersionBonding\ValueObject\ComposerBoundRuleConfiguration;
use ReflectionObject;
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
        $configurationTableRows = $this->createConfigurationTableRows();
        if ($tableRows === [] && $configurationTableRows === []) {
            $this->symfonyStyle->warning('No composer package bound rule is loaded');
            return Command::SUCCESS;
        }
        if ($tableRows !== []) {
            $this->symfonyStyle->title('Composer package bound rules');
            $this->symfonyStyle->table(['Rule', 'Package', 'Requires', 'Installed', 'Active'], $tableRows);
        }
        if ($configurationTableRows !== []) {
            $this->symfonyStyle->title('Composer package bound rule configuration');
            $this->symfonyStyle->table(['Rule', 'Package', 'Requires', 'Installed', 'Active', 'Configuration'], $configurationTableRows);
        }
        $allTableRows = array_merge($tableRows, $configurationTableRows);
        $activeCount = count(array_filter($allTableRows, static fn(array $tableRow): bool => $tableRow[4] === 'yes'));
        $this->symfonyStyle->note(sprintf('%d of %d composer package bound items are active', $activeCount, count($allTableRows)));
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
            $tableRows[] = [$this->printShortClassName(get_class($rector)), $packageName, $constraint, $installedVersion ?? '-', $isActive ? 'yes' : 'no'];
        }
        // sort by package name first, then by rule class
        usort($tableRows, static fn(array $firstTableRow, array $secondTableRow): int => [$firstTableRow[1], $firstTableRow[0]] <=> [$secondTableRow[1], $secondTableRow[0]]);
        return $tableRows;
    }
    /**
     * @return array<array{string, string, string, string, string, string}>
     */
    private function createConfigurationTableRows(): array
    {
        $composerBoundRuleConfigurations = SimpleParameterProvider::provideArrayParameter(Option::COMPOSER_BOUND_RULE_CONFIGURATIONS);
        $tableRows = [];
        foreach ($composerBoundRuleConfigurations as $composerBoundRuleConfiguration) {
            if (!$composerBoundRuleConfiguration instanceof ComposerBoundRuleConfiguration) {
                continue;
            }
            $packageName = $composerBoundRuleConfiguration->getPackageName();
            $installedVersion = $this->installedPackageResolver->resolvePackageVersion($packageName);
            $tableRows[] = [$this->printShortClassName($composerBoundRuleConfiguration->getRectorClass()), $packageName, $composerBoundRuleConfiguration->getVersionConstraint(), $installedVersion ?? '-', $composerBoundRuleConfiguration->isActive() ? 'yes' : 'no', $this->printConfiguration($composerBoundRuleConfiguration->getConfiguration())];
        }
        return $tableRows;
    }
    /**
     * @param mixed[] $configuration
     */
    private function printConfiguration(array $configuration): string
    {
        $printedItems = [];
        foreach ($configuration as $key => $value) {
            $printedValue = $this->printConfigurationValue($value);
            $printedItems[] = is_string($key) ? $key . ': ' . $printedValue : $printedValue;
        }
        return implode(\PHP_EOL, $printedItems);
    }
    /**
     * @param mixed $value
     */
    private function printConfigurationValue($value): string
    {
        if (is_object($value)) {
            $printedPropertyValues = [];
            $reflectionObject = new ReflectionObject($value);
            foreach ($reflectionObject->getProperties() as $reflectionProperty) {
                $printedPropertyValues[] = $this->printConfigurationValue($reflectionProperty->getValue($value));
            }
            return $this->printShortClassName(get_class($value)) . '(' . implode(', ', $printedPropertyValues) . ')';
        }
        if (is_array($value)) {
            $printedItems = array_map(\Closure::fromCallable([$this, 'printConfigurationValue']), $value);
            return '[' . implode(', ', $printedItems) . ']';
        }
        if (is_bool($value)) {
            return $value ? 'true' : 'false';
        }
        return (string) $value;
    }
    private function printShortClassName(string $className): string
    {
        return Strings::after($className, '\\', -1) ?? $className;
    }
}
