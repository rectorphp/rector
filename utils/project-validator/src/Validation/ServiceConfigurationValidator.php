<?php

declare(strict_types=1);

namespace Rector\Utils\ProjectValidator\Validation;

use Nette\Utils\Strings;
use Rector\Core\Exception\ShouldNotHappenException;
use ReflectionClass;
use Symplify\SmartFileSystem\SmartFileInfo;

final class ServiceConfigurationValidator
{
    public function validate(string $serviceClass, $configuration, SmartFileInfo $configFileInfo): void
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
