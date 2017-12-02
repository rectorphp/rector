<?php declare(strict_types=1);

namespace Rector\YamlParser\Rector\Contrib\Symfony;

use Rector\YamlParser\Contract\Rector\YamlRectorInterface;

final class AddAutowireRector implements YamlRectorInterface
{
    public function getCandidateKey(): string
    {
        return 'services';
    }

    /**
     * @param mixed[] $data
     * @return mixed[]
     */
    public function refactor(array $services): array
    {
        // add autowire if required or missing
        // remove explicit arguments @...

        return $services;
    }
}
