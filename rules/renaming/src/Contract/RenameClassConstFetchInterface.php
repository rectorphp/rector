<?php

declare(strict_types=1);

namespace Rector\Renaming\Contract;

interface RenameClassConstFetchInterface
{
    public function getOldClass(): string;

    public function getOldConstant(): string;

    public function getNewConstant(): string;
}
