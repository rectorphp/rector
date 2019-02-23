<?php declare(strict_types=1);

namespace Rector\Console\Command;

use Rector\Console\Shell;
use Rector\Contract\Rector\RectorInterface;
use Rector\Php\TypeAnalyzer;
use ReflectionClass;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Yaml\Yaml;
use Symplify\PackageBuilder\Console\Command\CommandNaming;

final class ShowCommand extends AbstractCommand
{
    /**
     * @var SymfonyStyle
     */
    private $symfonyStyle;

    /**
     * @var RectorInterface[]
     */
    private $rectors = [];

    /**
     * @var TypeAnalyzer
     */
    private $typeAnalyzer;

    /**
     * @param RectorInterface[] $rectors
     */
    public function __construct(SymfonyStyle $symfonyStyle, array $rectors, TypeAnalyzer $typeAnalyzer)
    {
        $this->symfonyStyle = $symfonyStyle;
        $this->rectors = $rectors;

        parent::__construct();
        $this->typeAnalyzer = $typeAnalyzer;
    }

    protected function configure(): void
    {
        $this->setName(CommandNaming::classToName(self::class));
        $this->setDescription('Show loaded Rectors with their configuration');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        sort($this->rectors);

        foreach ($this->rectors as $rector) {
            $this->symfonyStyle->writeln(' * ' . get_class($rector));
            $configuration = $this->resolveConfiguration($rector);
            if ($configuration === []) {
                continue;
            }

            $configurationYamlContent = Yaml::dump($configuration, 10, 4, Yaml::DUMP_MULTI_LINE_LITERAL_BLOCK);

            $lines = explode(PHP_EOL, $configurationYamlContent);
            $indentedContent = '      ' . implode(PHP_EOL . '      ', $lines);

            $this->symfonyStyle->writeln($indentedContent);
        }

        $this->symfonyStyle->success(sprintf('%d loaded Rectors', count($this->rectors)));

        return Shell::CODE_SUCCESS;
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
            $parameterType = (string) $reflectionParameter->getType();
            if (! $this->typeAnalyzer->isPhpReservedType($parameterType)) {
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
}
