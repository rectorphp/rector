<?php

declare(strict_types=1);

namespace Rector\Core\Configuration\MinimalVersionChecker;

use Nette\Utils\FileSystem;

final class ComposerJsonReader
{
    /**
     * @var string
     */
    private $filename;

    public function __construct(string $composerJsonFilename)
    {
        $this->filename = $composerJsonFilename;
    }

    public function read(): string
    {
        return FileSystem::read($this->filename);
    }
}
