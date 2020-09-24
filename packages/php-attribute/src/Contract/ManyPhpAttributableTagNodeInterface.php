<?php

declare(strict_types=1);

namespace Rector\PhpAttribute\Contract;

interface ManyPhpAttributableTagNodeInterface
{
    /**
     * @return array<string, mixed[]>
     */
    public function provide(): array;
}
