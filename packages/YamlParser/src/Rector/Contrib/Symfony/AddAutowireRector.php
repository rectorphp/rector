<?php declare(strict_types=1);

namespace Rector\YamlParser\Rector\Contrib\Symfony;

use Nette\Utils\Strings;
use Rector\YamlParser\Contract\Rector\YamlRectorInterface;

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
    public function refactor(array $services): array
    {
        if (isset($services['_defaults']['autowire'])) {
            return $services;
        }

        $defaultsAutowire = [
            '_defaults' => [
                'autowire' => true,
            ],
        ];
        $services = array_merge($defaultsAutowire, $services);

        foreach ($services as $name => $service) {
            if (! isset($service['arguments'])) {
                continue;
            }

            $nonAutowireableArguments = $this->removeServiceReferencesFromArguments($service['arguments']);
            if (count($nonAutowireableArguments) === 0) {
                unset($service['arguments']);
            } else {
                $service['arguments'] = $nonAutowireableArguments;
            }

            $services[$name] = $service;
        }

        return $services;
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
