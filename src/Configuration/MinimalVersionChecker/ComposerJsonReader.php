<?php

declare(strict_types=1);

namespace Rector\Core\Configuration\MinimalVersionChecker;

use Symplify\SmartFileSystem\SmartFileSystem;

final class ComposerJsonReader
{
    /**
     * @var string
     */
    private $filename;

    /**
     * @var SmartFileSystem
     */
    private $smartFileSystem;

    public function __construct(string $composerJsonFilename)
    {
        $this->filename = $composerJsonFilename;
        $this->smartFileSystem = new SmartFileSystem();
    }

    public function read(): string
    {
        return $this->smartFileSystem->readFile($this->filename);
    }
}
