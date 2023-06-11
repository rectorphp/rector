<?php

declare (strict_types=1);
namespace Rector\Caching\ValueObject;

final class CacheFilePaths
{
    /**
     * @readonly
     * @var string
     */
    private $firstDirectory;
    /**
     * @readonly
     * @var string
     */
    private $secondDirectory;
    /**
     * @readonly
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
