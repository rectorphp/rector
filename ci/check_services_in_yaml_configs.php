<?php

declare(strict_types=1);

use Nette\Utils\Strings;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\NodeTypeResolver\ClassExistenceStaticHelper;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;
use Symfony\Component\Yaml\Yaml;

require __DIR__ . '/../vendor/autoload.php';

$serviceExistenceConfigChecker = new ServiceExistenceConfigChecker();
$serviceExistenceConfigChecker->check();

final class ServiceExistenceConfigChecker
{
    /**
     * @var YamlConfigFileProvider
     */
    private $yamlConfigFileProvider;

    /**
     * @var ServiceConfigurationValidator
     */
    private $serviceConfigurationValidator;

    public function __construct()
    {
        $this->yamlConfigFileProvider = new YamlConfigFileProvider();
        $this->serviceConfigurationValidator = new ServiceConfigurationValidator();
    }

    public function check(): void
    {
        foreach ($this->yamlConfigFileProvider->provider() as $configFileInfo) {
            $yamlContent = Yaml::parseFile($configFileInfo->getRealPath());
            if (! isset($yamlContent['services'])) {
                continue;
            }

            foreach ($yamlContent['services'] as $service => $serviceConfiguration) {
                // configuration → skip
                if (Strings::startsWith($service, '_')) {
                    continue;
                }

                // autodiscovery → skip
                if (Strings::endsWith($service, '\\')) {
                    continue;
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

        echo 'All configs have existing services - good job!' . PHP_EOL;
    }
}

final class YamlConfigFileProvider
{
    /**
     * @return SplFileInfo[]
     */
    public function provider(): array
    {
        $finder = (new Finder())->name('*.yaml')
            ->in(__DIR__ . '/../config')
            ->files();

        return iterator_to_array($finder->getIterator());
    }
}

final class ServiceConfigurationValidator
{
    public function validate(string $serviceClass, $configuration, SplFileInfo $configFileInfo): void
    {
        if (! is_array($configuration)) {
            return;
        }

        foreach (array_keys($configuration) as $key) {
            if (! $this->isArgumentName($key)) {
                continue;
            }

            $constructorParameterNames = $this->resolveClassConstructorArgumentNames($serviceClass);
            if (in_array($key, $constructorParameterNames, true)) {
                continue;
            }

            throw new ShouldNotHappenException(sprintf(
                'Service "%s" has unused argument "%s" in config "%s".%sCorrect it to one of existing arguments "%s".',
                $serviceClass,
                $key,
                $configFileInfo->getRealPath(),
                PHP_EOL,
                implode('", "', $constructorParameterNames)
            ));
        }
    }

    private function isArgumentName($key): bool
    {
        if (! is_string($key)) {
            return false;
        }

        return Strings::startsWith($key, '$');
    }

    /**
     * @return string[]
     */
    private function resolveClassConstructorArgumentNames(string $class): array
    {
        $reflectionClass = new ReflectionClass($class);
        $constructorReflection = $reflectionClass->getConstructor();

        if ($constructorReflection === null) {
            return [];
        }

        $constructorParameterNames = [];
        foreach ($constructorReflection->getParameters() as $parameterReflection) {
            $constructorParameterNames[] = '$' . $parameterReflection->getName();
        }

        sort($constructorParameterNames);

        return $constructorParameterNames;
    }
}
