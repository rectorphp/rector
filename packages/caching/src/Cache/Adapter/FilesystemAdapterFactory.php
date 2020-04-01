<?php

declare(strict_types=1);

namespace Rector\Caching\Cache\Adapter;

use Nette\Utils\Strings;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;

final class FilesystemAdapterFactory
{
    public function create(): FilesystemAdapter
    {
        return new FilesystemAdapter(
            // unique per project
            Strings::webalize(getcwd()),
            0,
            sys_get_temp_dir() . '/_rector_cached_files'
        );
    }
}
