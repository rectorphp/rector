<?php

declare (strict_types=1);
namespace Rector\Symfony\ValueObject;

final class ClassNameAndFilePath
{
    /**
     * @readonly
     * @var string
     */
    private $className;
    /**
     * @readonly
     * @var string
     */
    private $filePath;
    public function __construct(string $className, string $filePath)
    {
        $this->className = $className;
        $this->filePath = $filePath;
    }
    public function getClassName() : string
    {
        return $this->className;
    }
    public function getFilePath() : string
    {
        return $this->filePath;
    }
}
