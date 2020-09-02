<?php

declare(strict_types=1);

namespace Rector\Core\Console\Command;

use Rector\Core\Application\ActiveRectorsProvider;
use Rector\Core\Contract\Rector\ConfigurableRectorInterface;
use Rector\Core\Contract\Rector\RectorInterface;
use Rector\Core\NeonYaml\YamlPrinter;
use ReflectionClass;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symplify\PackageBuilder\Console\Command\CommandNaming;
use Symplify\PackageBuilder\Console\ShellCode;

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

    public function __construct(
        SymfonyStyle $symfonyStyle,
        ActiveRectorsProvider $activeRectorsProvider,
        YamlPrinter $yamlPrinter
    ) {
        $this->symfonyStyle = $symfonyStyle;
        $this->yamlPrinter = $yamlPrinter;
        $this->activeRectorsProvider = $activeRectorsProvider;

        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setName(CommandNaming::classToName(self::class));
        $this->setDescription('Show loaded Rectors with their configuration');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $activeRectors = $this->activeRectorsProvider->provide();

        foreach ($activeRectors as $rector) {
            $this->symfonyStyle->writeln(' * ' . get_class($rector));
            $this->printConfiguration($rector);
        }

        $message = sprintf('%d loaded Rectors', count($activeRectors));
        $this->symfonyStyle->success($message);

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
}
