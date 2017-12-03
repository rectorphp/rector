<?php declare(strict_types=1);

namespace Rector\YamlParser\Rector\Contrib\Symfony;

use Rector\YamlParser\Contract\Rector\YamlRectorInterface;

final class PSR4AutoloadRector implements YamlRectorInterface
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
            // find namespace => resource ideal match
        }

        // add those matches
        foreach ($services as $name => $service) {
        }

        // remove already loaded classes
        foreach ($services as $name => $service) {
        }


        return $services;
    }
}
