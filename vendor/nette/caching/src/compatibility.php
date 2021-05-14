<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */
declare (strict_types=1);
namespace RectorPrefix20210514\Nette\Caching;

if (\false) {
    /** @deprecated use Nette\Caching\BulkReader */
    interface IBulkReader extends \RectorPrefix20210514\Nette\Caching\BulkReader
    {
    }
} elseif (!\interface_exists(\RectorPrefix20210514\Nette\Caching\IBulkReader::class)) {
    \class_alias(\RectorPrefix20210514\Nette\Caching\BulkReader::class, \RectorPrefix20210514\Nette\Caching\IBulkReader::class);
}
if (\false) {
    /** @deprecated use Nette\Caching\Storage */
    interface IStorage extends \RectorPrefix20210514\Nette\Caching\Storage
    {
    }
} elseif (!\interface_exists(\RectorPrefix20210514\Nette\Caching\IStorage::class)) {
    \class_alias(\RectorPrefix20210514\Nette\Caching\Storage::class, \RectorPrefix20210514\Nette\Caching\IStorage::class);
}
namespace RectorPrefix20210514\Nette\Caching\Storages;

if (\false) {
    /** @deprecated use Nette\Caching\Storages\Journal */
    interface IJournal extends \RectorPrefix20210514\Nette\Caching\Storages\Journal
    {
    }
} elseif (!\interface_exists(\RectorPrefix20210514\Nette\Caching\Storages\IJournal::class)) {
    \class_alias(\RectorPrefix20210514\Nette\Caching\Storages\Journal::class, \RectorPrefix20210514\Nette\Caching\Storages\IJournal::class);
}
