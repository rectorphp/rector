<?php

declare(strict_types=1);

namespace Rector\Utils\ProjectValidator\Command;

use Nette\Utils\Strings;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\NodeTypeResolver\ClassExistenceStaticHelper;
use Rector\Utils\ProjectValidator\Validation\ServiceConfigurationValidator;
use Rector\Utils\ProjectValidator\Yaml\YamlConfigFileProvider;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Yaml\Yaml;
use Symplify\PackageBuilder\Console\Command\CommandNaming;
use Symplify\PackageBuilder\Console\ShellCode;
use Symplify\SmartFileSystem\SmartFileInfo;

final class ValidateServicesInSetsCommand extends Command
{
    /**
     * @var SymfonyStyle
     */
    private $symfonyStyle;

    /**
     * @var ServiceConfigurationValidator
     */
    private $serviceConfigurationValidator;

    /**
     * @var YamlConfigFileProvider
     */
    private $yamlConfigFileProvider;

    public function __construct(
        SymfonyStyle $symfonyStyle,
        ServiceConfigurationValidator $serviceConfigurationValidator,
        YamlConfigFileProvider $yamlConfigFileProvider
    ) {
        $this->symfonyStyle = $symfonyStyle;
        $this->serviceConfigurationValidator = $serviceConfigurationValidator;
        $this->yamlConfigFileProvider = $yamlConfigFileProvider;

        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setName(CommandNaming::classToName(self::class));
        $this->setDescription('[CI] Validate services in sets');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        foreach ($this->yamlConfigFileProvider->provider() as $configFileInfo) {
            $this->symfonyStyle->note(sprintf('Validating config "%s"', $configFileInfo->getRelativeFilePathFromCwd()));

            $yamlContent = Yaml::parseFile($configFileInfo->getRealPath());
            if (! isset($yamlContent['services'])) {
                continue;
            }

            foreach ($yamlContent['services'] as $service => $serviceConfiguration) {
                $this->validateService($service, $serviceConfiguration, $configFileInfo);
            }
        }

        $this->symfonyStyle->success('All configs have existing services');

        return ShellCode::SUCCESS;
    }

    private function validateService($service, $serviceConfiguration, SmartFileInfo $configFileInfo): void
    {
        // configuration → skip
        if (Strings::startsWith($service, '_')) {
            return;
        }

        // autodiscovery → skip
        if (Strings::endsWith($service, '\\')) {
            return;
        }

        if (! ClassExistenceStaticHelper::doesClassLikeExist($service)) {
            throw new ShouldNotHappenException(sprintf(
                'Service "%s" from config "%s" was not found. Check if it really exists or is even autoload, please',
                $service,
                $configFileInfo->getRealPath()
            ));
        }

        $this->serviceConfigurationValidator->validate($service, $serviceConfiguration, $configFileInfo);
    }
}
