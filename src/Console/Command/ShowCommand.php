<?php

declare(strict_types=1);

namespace Rector\Core\Console\Command;

use Rector\Core\Contract\Rector\RectorInterface;
use Rector\Core\NeonYaml\YamlPrinter;
use Rector\Core\Php\TypeAnalyzer;
use Rector\PostRector\Contract\Rector\PostRectorInterface;
use Rector\Utils\DoctrineAnnotationParserSyncer\Contract\Rector\ClassSyncerRectorInterface;
use ReflectionClass;
use ReflectionNamedType;
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
     * @var TypeAnalyzer
     */
    private $typeAnalyzer;

    /**
     * @var YamlPrinter
     */
    private $yamlPrinter;

    /**
     * @param RectorInterface[] $rectors
     */
    public function __construct(
        SymfonyStyle $symfonyStyle,
        array $rectors,
        TypeAnalyzer $typeAnalyzer,
        YamlPrinter $yamlPrinter
    ) {
        $this->symfonyStyle = $symfonyStyle;
        $this->rectors = $rectors;
        $this->typeAnalyzer = $typeAnalyzer;
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
            $configuration = $this->resolveConfiguration($rector);
            if ($configuration === []) {
                continue;
            }

            $configurationYamlContent = $this->yamlPrinter->printYamlToString($configuration);

            $lines = explode(PHP_EOL, $configurationYamlContent);
            $indentedContent = '      ' . implode(PHP_EOL . '      ', $lines);

            $this->symfonyStyle->writeln($indentedContent);
        }

        $this->symfonyStyle->success(sprintf('%d loaded Rectors', count($rectors)));

        return ShellCode::SUCCESS;
    }

    /**
     * Resolve configuration by convention
     * @return mixed[]
     */
    private function resolveConfiguration(RectorInterface $rector): array
    {
        $rectorReflection = new ReflectionClass($rector);

        $constructorReflection = $rectorReflection->getConstructor();
        if ($constructorReflection === null) {
            return [];
        }

        $configuration = [];
        foreach ($constructorReflection->getParameters() as $reflectionParameter) {
            $parameterType = $reflectionParameter->getType();
            $parameterTypeName = (string) ($parameterType instanceof ReflectionNamedType ? $parameterType->getName() : null);
            if (! $this->typeAnalyzer->isPhpReservedType($parameterTypeName)) {
                continue;
            }

            if (! $rectorReflection->hasProperty($reflectionParameter->getName())) {
                continue;
            }

            $propertyReflection = $rectorReflection->getProperty($reflectionParameter->getName());
            $propertyReflection->setAccessible(true);

            $configurationValue = $propertyReflection->getValue($rector);
            $configuration[$reflectionParameter->getName()] = $configurationValue;
        }

        return $configuration;
    }

    /**
     * @param RectorInterface[] $rectors
     * @return RectorInterface[]
     */
    private function filterAndSortRectors(array $rectors): array
    {
        sort($rectors);

        return array_filter($rectors, function (RectorInterface $rector) {
            // utils rules
            if ($rector instanceof ClassSyncerRectorInterface) {
                return false;
            }

            // skip as internal and always run
            return ! $rector instanceof PostRectorInterface;
        });
    }
}
