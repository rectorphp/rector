<?php

declare(strict_types=1);

namespace Rector\Core\Console\Command;

use Rector\Core\Configuration\Option;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symplify\PackageBuilder\Console\ShellCode;
use Symplify\PhpConfigPrinter\YamlToPhpConverter;
use Symplify\SmartFileSystem\SmartFileSystem;

final class CreateConfigCommand extends AbstractCommand
{
    /**
     * @var SmartFileSystem
     */
    private $smartFileSystem;

    /**
     * @var YamlToPhpConverter
     */
    private $yamlToPhpConverter;

    /**
     * @var SymfonyStyle
     */
    private $symfonyStyle;

    public function __construct(SmartFileSystem $smartFileSystem, YamlToPhpConverter $yamlToPhpConverter, SymfonyStyle $symfonyStyle)
    {
        parent::__construct();

        $this->smartFileSystem = $smartFileSystem;
        $this->yamlToPhpConverter = $yamlToPhpConverter;
        $this->symfonyStyle = $symfonyStyle;
    }

    protected function configure(): void
    {
        $this->setDescription('Generate configuration file using parameters in YAML format');
        $this->addArgument(
            Option::SOURCE,
            InputArgument::REQUIRED,
            'The PHP file with the configuration in YAML format.'
        );
        $this->addOption(
            'output', //Option::OUTPUT,
            'o',
            InputOption::VALUE_REQUIRED,
            'The name of the config file to generate. Default: rector.php.',
            'rector.php'
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $outputFilename = (string) $input->getOption('output'/*Option::OUTPUT*/);
        $outputFile = getcwd() . '/' . $outputFilename;
        $rectorConfigFiles = $this->smartFileSystem->exists($outputFile);

        if (! $rectorConfigFiles) {
            $source = $input->getArgument(Option::SOURCE);
            $phpFileContent = $this->yamlToPhpConverter->convertYamlArray([
                'parameters' => [
                    'key' => 'value',
                ],
                'services' => [
                    '_defaults' => [
                        'autowire' => true,
                        'autoconfigure' => true,
                    ],
                ],
            ]);
            $this->smartFileSystem->dumpFile($outputFile, $phpFileContent);
            $this->symfonyStyle->success(sprintf(
                '"%s" config file has been generated successfully!',
                $outputFilename
            ));
        } else {
            $this->symfonyStyle->error(sprintf(
                'Config file not generated. A "%s" configuration file already exists',
                $outputFilename
            ));
        }

        return ShellCode::SUCCESS;
    }
}
