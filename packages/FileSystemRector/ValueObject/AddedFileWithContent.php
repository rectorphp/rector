<?php

declare (strict_types=1);
namespace Rector\FileSystemRector\ValueObject;

use Rector\Core\Exception\ShouldNotHappenException;
use Rector\FileSystemRector\Contract\AddedFileInterface;
final class AddedFileWithContent implements AddedFileInterface
{
    /**
     * @readonly
     * @var string
     */
    private $filePath;
    /**
     * @readonly
     * @var string
     */
    private $fileContent;
    public function __construct(string $filePath, string $fileContent)
    {
        $this->filePath = $filePath;
        $this->fileContent = $fileContent;
        if ($filePath === $fileContent) {
            throw new ShouldNotHappenException('File path and content are the same, probably a bug');
        }
    }
    public function getRealPath() : string
    {
        $realPath = \realpath($this->filePath);
        if ($realPath === \false) {
            throw new ShouldNotHappenException();
        }
        return $realPath;
    }
    public function getFilePath() : string
    {
        return $this->filePath;
    }
    public function getFileContent() : string
    {
        return $this->fileContent;
    }
}
