<?php declare(strict_types=1);

namespace Rector\YamlParser\Contract\Rector;

interface YamlRectorInterface
{
    public function getCondidateKey(): string;

    /**
     * @param mixed[] $data
     * @return mixed[]
     */
    public function refactor(array $data): array;
}
