<?php

declare (strict_types=1);
namespace Rector\Caching\ValueObject;

final class CacheFilePaths
{
    /**
     * @var string
     */
    private $firstDirectory;
    /**
     * @var string
     */
    private $secondDirectory;
    /**
     * @var string
     */
    private $filePath;
    public function __construct(string $firstDirectory, string $secondDirectory, string $filePath)
    {
        $this->firstDirectory = $firstDirectory;
        $this->secondDirectory = $secondDirectory;
        $this->filePath = $filePath;
    }
    public function getFirstDirectory() : string
    {
        return $this->firstDirectory;
    }
    public function getSecondDirectory() : string
    {
        return $this->secondDirectory;
    }
    public function getFilePath() : string
    {
        return $this->filePath;
    }
}
