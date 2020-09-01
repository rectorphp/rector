<?php

declare(strict_types=1);

namespace Rector\Core\Console\Command;

use Rector\Core\Contract\Rector\ConfigurableRectorInterface;
use Rector\Core\Contract\Rector\RectorInterface;
use Rector\Core\NeonYaml\YamlPrinter;
use Rector\PostRector\Contract\Rector\PostRectorInterface;
use Rector\RectorGenerator\Contract\InternalRectorInterface;
use ReflectionClass;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symplify\PackageBuilder\Console\Command\CommandNaming;
use Symplify\PackageBuilder\Console\ShellCode;

final class ShowCommand extends AbstractCommand
{
    /**
     * @var RectorInterface[]
     */
    private $rectors = [];

    /**
     * @var SymfonyStyle
     */
    private $symfonyStyle;

    /**
     * @var YamlPrinter
     */
    private $yamlPrinter;

    /**
     * @param RectorInterface[] $rectors
     */
    public function __construct(SymfonyStyle $symfonyStyle, array $rectors, YamlPrinter $yamlPrinter)
    {
        $this->symfonyStyle = $symfonyStyle;
        $this->rectors = $rectors;
        $this->yamlPrinter = $yamlPrinter;

        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setName(CommandNaming::classToName(self::class));
        $this->setDescription('Show loaded Rectors with their configuration');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $rectors = $this->filterAndSortRectors($this->rectors);

        foreach ($rectors as $rector) {
            $this->symfonyStyle->writeln(' * ' . get_class($rector));
            $this->printConfiguration($rector);
        }
        $message = sprintf('%d loaded Rectors', count($rectors));

        $this->symfonyStyle->success($message);

        return ShellCode::SUCCESS;
    }

    /**
     * @param RectorInterface[] $rectors
     * @return RectorInterface[]
     */
    private function filterAndSortRectors(array $rectors): array
    {
        sort($rectors);

        return array_filter($rectors, function (RectorInterface $rector): bool {
            // utils rules
            if ($rector instanceof InternalRectorInterface) {
                return false;
            }

            // skip as internal and always run
            return ! $rector instanceof PostRectorInterface;
        });
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
