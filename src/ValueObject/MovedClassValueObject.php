<?php declare(strict_types=1);

namespace Rector\ValueObject;

final class MovedClassValueObject
{
    /**
     * @var string
     */
    private $oldPath;

    /**
     * @var string
     */
    private $newPath;

    /**
     * @var string
     */
    private $fileContent;

    public function __construct(string $oldPath, string $newPath, string $fileContent)
    {
        $this->oldPath = $oldPath;
        $this->newPath = $newPath;
        $this->fileContent = $fileContent;
    }

    public function getOldPath(): string
    {
        return $this->oldPath;
    }

    public function getNewPath(): string
    {
        return $this->newPath;
    }

    public function getFileContent(): string
    {
        return $this->fileContent;
    }
}
