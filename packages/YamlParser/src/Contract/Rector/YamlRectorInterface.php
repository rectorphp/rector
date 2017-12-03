<?php declare(strict_types=1);

namespace Rector\YamlParser\Contract\Rector;

use SplFileInfo;

interface YamlRectorInterface
{
    public function getCandidateKey(): string;

    /**
     * @param mixed[] $data
     * @return mixed[]
     */
    public function refactor(array $data, SplFileInfo $fileInfo): array;
}
