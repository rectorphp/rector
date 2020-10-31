<?php
declare(strict_types=1);

namespace Rector\Core\ValueObject;

final class FilePathWithContent
{
    /**
     * @var string
     */
    private $filePath;

    /**
     * @var string
     */
    private $fileContent;

    public function __construct(string $filePath, string $fileContent)
    {
        $this->filePath = $filePath;
        $this->fileContent = $fileContent;
    }

    public function getFilePath(): string
    {
        return $this->filePath;
    }

    public function getFileContent(): string
    {
        return $this->fileContent;
    }
}
