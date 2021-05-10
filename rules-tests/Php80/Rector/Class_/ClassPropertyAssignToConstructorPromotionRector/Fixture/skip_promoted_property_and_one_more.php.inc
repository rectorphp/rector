<?php

namespace Rector\Tests\Php80\Rector\Class_\ClassPropertyAssignToConstructorPromotionRector\Fixture;

final class SkipPromotedPropertyAndOneMore
{
    /**
     * @var string
     */
    private $originalFile;

    public function __construct(private string $fileContent)
    {
        $this->originalFile = $fileContent;
    }

    public function getOriginalFile(): string
    {
        return $this->originalFile;
    }

    public function getFileContent(): string
    {
        return $this->fileContent;
    }
}
