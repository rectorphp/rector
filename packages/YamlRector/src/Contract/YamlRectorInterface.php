<?php declare(strict_types=1);

namespace Rector\YamlRector\Contract;

use Rector\RectorDefinition\RectorDefinition;

interface YamlRectorInterface
{
    public function getDefinition(): RectorDefinition;

    public function isCandidate(string $content): bool;

    public function refactor(string $content): string;
}
