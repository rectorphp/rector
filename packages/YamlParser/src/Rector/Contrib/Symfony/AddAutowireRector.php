<?php declare(strict_types=1);

namespace Rector\YamlParser\Rector\Contrib\Symfony;

use Nette\Utils\Strings;
use Rector\YamlParser\Contract\Rector\YamlRectorInterface;
use SplFileInfo;

final class AddAutowireRector implements YamlRectorInterface
{
    public function getCandidateKey(): string
    {
        return 'services';
    }

    /**
     * @param mixed[] $services
     * @return mixed[]
     */
    public function refactor(array $services, SplFileInfo $fileInfo): array
    {
        if (isset($services['_defaults']['autowire'])) {
            return $services;
        }

        $services = $this->prependDefaultsAutowire($services);

        foreach ($services as $name => $service) {
            if (! isset($service['arguments'])) {
                continue;
            }

            $services[$name] = $this->processService($service);
        }

        return $services;
    }

    /**
     * @param mixed[] $services
     * @return mixed[]
     */
    private function prependDefaultsAutowire(array $services): array
    {
        $defaultsAutowire = [
            '_defaults' => [
                'autowire' => true,
            ],
        ];

        return array_merge($defaultsAutowire, $services);
    }

    /**
     * @param mixed[] $service
     * @return mixed[]|string
     */
    private function processService(array $service)
    {
        $nonAutowireableArguments = $this->removeServiceReferencesFromArguments($service['arguments']);
        if (count($nonAutowireableArguments) === 0) {
            unset($service['arguments']);
        } else {
            $service['arguments'] = $nonAutowireableArguments;
        }

        // no arguments, use null
        if (count($service) === 0) {
            return '~';
        }

        return $service;
    }

    /**
     * @param mixed[] $arguments
     * @return mixed[]
     */
    private function removeServiceReferencesFromArguments(array $arguments): array
    {
        foreach ($arguments as $key => $argument) {
            if (Strings::startsWith($argument, '@')) {
                unset($arguments[$key]);
            }
        }

        return $arguments;
    }
}
