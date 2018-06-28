<?php declare(strict_types=1);

namespace Rector\YamlRector\Contract;

interface YamlRectorInterface
{
    public function isCandidate(string $content): bool;

    public function refactor(string $content): string;
}
