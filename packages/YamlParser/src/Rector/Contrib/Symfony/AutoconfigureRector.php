<?php declare(strict_types=1);

namespace Rector\YamlParser\Rector\Contrib\Symfony;

use Rector\YamlParser\Contract\Rector\YamlRectorInterface;

final class AutoconfigureRector implements YamlRectorInterface
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
        // skip if autoconfigure already exists

        // find class with system tags

        // remove system only tags

        // prepend autoconfigure

        return $services;
    }
}
