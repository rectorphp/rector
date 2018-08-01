<?php declare(strict_types=1);

namespace Rector\YamlRector\Contract;

use Rector\Contract\Rector\RectorInterface;

interface YamlRectorInterface extends RectorInterface
{
    public function isCandidate(string $content): bool;

    public function refactor(string $content): string;
}
