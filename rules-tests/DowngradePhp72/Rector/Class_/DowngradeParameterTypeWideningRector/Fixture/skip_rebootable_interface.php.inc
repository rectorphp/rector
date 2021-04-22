<?php

interface SkipRebootableInterface
{
    /**
     * @param string|null $warmupDir pass null to reboot in the regular build directory
     */
    public function reboot(?string $warmupDir);
}

final class SomeKernel implements RebootableInterface
{
    /**
     * {@inheritdoc}
     */
    public function reboot(?string $warmupDir)
    {
    }
}
