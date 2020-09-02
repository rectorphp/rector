<?php

declare(strict_types=1);

namespace Rector\Core\Console\Command;

use Rector\Core\Application\ActiveRectorsProvider;
use Rector\Core\Configuration\Option;
use Rector\Core\Contract\Rector\ConfigurableRectorInterface;
use Rector\Core\Contract\Rector\RectorInterface;
use Rector\Core\NeonYaml\YamlPrinter;
use ReflectionClass;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symplify\PackageBuilder\Console\Command\CommandNaming;
use Symplify\PackageBuilder\Console\ShellCode;
use Symplify\PackageBuilder\Parameter\ParameterProvider;

final class ShowCommand extends AbstractCommand
{
    /**
     * @var SymfonyStyle
     */
    private $symfonyStyle;

    /**
     * @var YamlPrinter
     */
    private $yamlPrinter;

    /**
     * @var ActiveRectorsProvider
     */
    private $activeRectorsProvider;

    /**
     * @var ParameterProvider
     */
    private $parameterProvider;

    public function __construct(
        SymfonyStyle $symfonyStyle,
        ActiveRectorsProvider $activeRectorsProvider,
        ParameterProvider $parameterProvider,
        YamlPrinter $yamlPrinter
    ) {
        $this->symfonyStyle = $symfonyStyle;
        $this->yamlPrinter = $yamlPrinter;
        $this->activeRectorsProvider = $activeRectorsProvider;
        $this->parameterProvider = $parameterProvider;

        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setName(CommandNaming::classToName(self::class));
        $this->setDescription('Show loaded Rectors with their configuration');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->reportLoadedRectors();
        $this->reportLoadedSets();

        return ShellCode::SUCCESS;
    }

    private function printConfiguration(RectorInterface $rector): void
    {
        $configuration = $this->resolveConfiguration($rector);
        if ($configuration === []) {
            return;
        }

        $configurationYamlContent = $this->yamlPrinter->printYamlToString($configuration);

        $lines = explode(PHP_EOL, $configurationYamlContent);
        $indentedContent = '      ' . implode(PHP_EOL . '      ', $lines);

        $this->symfonyStyle->writeln($indentedContent);
    }

    /**
     * Resolve configuration by convention
     * @return mixed[]
     */
    private function resolveConfiguration(RectorInterface $rector): array
    {
        if (! $rector instanceof ConfigurableRectorInterface) {
            return [];
        }

        $reflectionClass = new ReflectionClass($rector);

        $configuration = [];
        foreach ($reflectionClass->getProperties() as $reflectionProperty) {
            $reflectionProperty->setAccessible(true);

            $configurationValue = $reflectionProperty->getValue($rector);

            // probably service → skip
            if (is_object($configurationValue)) {
                continue;
            }

            $configuration[$reflectionProperty->getName()] = $configurationValue;
        }

        return $configuration;
    }

    private function reportLoadedRectors(): void
    {
        $activeRectors = $this->activeRectorsProvider->provide();

        $rectorCount = count($activeRectors);

        if ($rectorCount > 0) {
            $this->symfonyStyle->title('Loaded Rector rules');

            foreach ($activeRectors as $rector) {
                $this->symfonyStyle->writeln(' * ' . get_class($rector));
                $this->printConfiguration($rector);
            }

            $message = sprintf('%d loaded Rectors', $rectorCount);
            $this->symfonyStyle->success($message);
        } else {
            $warningMessage = sprintf(
                'No Rectors were loaded.%sAre sure your "rector.php" config is in the root?%sTry "--config <path>" option to include it.',
                PHP_EOL . PHP_EOL,
                PHP_EOL
            );
            $this->symfonyStyle->warning($warningMessage);
        }
    }

    private function reportLoadedSets(): void
    {
        $sets = $this->parameterProvider->provideParameter(Option::SETS) ?? [];
        if ($sets === []) {
            return;
        }

        $this->symfonyStyle->newLine(2);

        $this->symfonyStyle->title('Loaded Sets');

        sort($sets);

        foreach ($sets as $set) {
            $filename = realpath($set);
            $this->symfonyStyle->writeln(' * ' . $filename);
        }

        $message = sprintf('%d loaded sets', count($sets));
        $this->symfonyStyle->success($message);
    }
}
