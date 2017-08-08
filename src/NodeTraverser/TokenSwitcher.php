<?php declare(strict_types=1);

namespace Rector\NodeTraverser;

final class TokenSwitcher
{
    /**
     * @var bool
     */
    private $isEnabled = false;

    public function enable(): void
    {
        $this->isEnabled = true;
    }

    public function disable(): void
    {
        $this->isEnabled = false;
    }

    public function isEnabled(): bool
    {
        return $this->isEnabled;
    }
}
