<?php declare(strict_types=1);

namespace Rector\YamlParser\Rector;

use Rector\YamlParser\Contract\Rector\YamlRectorInterface;

final class NamedServiceToClassRector implements YamlRectorInterface
{
    public function getCondidateKey(): string
    {
        return 'services';
    }

    /**
     * @param mixed[] $data
     * @return mixed[]
     */
    public function refactor(array $data): array
    {
        dump($data);
        die;
    }
}
