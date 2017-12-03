<?php declare(strict_types=1);

namespace Rector\YamlParser\Rector\Contrib\Symfony;

use Rector\YamlParser\Contract\Rector\YamlRectorInterface;
use SplFileInfo;

/**
 * Turn custom names of services,
 * to class based ones.
 *
 * Before:
 *
 * services:
 *      some_class:
 *          class: SomeClass
 *
 * After:
 *
 * services:
 *      SomeClass: ~
 */
final class NamedServiceToClassRector implements YamlRectorInterface
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
        $newServices = [];

        foreach ($services as $name => $service) {
            if (! isset($service['class'])) {
                continue;
            }

            if (! is_string($name) && ! $service['class']) {
                continue;
            }

            unset($services[$name]);
            $newServices[$service['class']] = '~';
        }

        return $newServices;
    }
}
