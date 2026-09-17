<?php

declare (strict_types=1);
namespace RectorPrefix202609\Entropy\FileSystem;

use RectorPrefix202609\Entropy\Utils\FileSystem;
use SplFileInfo;
final class FileInfo extends SplFileInfo
{
    /**
     * @readonly
     */
    private string $relativePath = '';
    /**
     * @readonly
     */
    private string $relativePathname = '';
    public function __construct(string $filePath, string $relativePath = '', string $relativePathname = '')
    {
        $this->relativePath = $relativePath;
        $this->relativePathname = $relativePathname;
        parent::__construct($filePath);
    }
    /**
     * @api
     */
    public function getContents(): string
    {
        return FileSystem::read($this->getPathname());
    }
    /**
     * @api
     */
    public function getRelativePath(): string
    {
        return $this->relativePath;
    }
    /**
     * @api
     */
    public function getRelativePathname(): string
    {
        return $this->relativePathname;
    }
}
