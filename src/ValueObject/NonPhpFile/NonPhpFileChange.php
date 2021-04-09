<?php
declare(strict_types=1);

namespace Rector\Core\ValueObject\NonPhpFile;

final class NonPhpFileChange
{
    /**
     * @var string
     */
    private $oldContent;

    /**
     * @var string
     */
    private $newContent;

    public function __construct(string $oldContent, string $newContent)
    {
        $this->oldContent = $oldContent;
        $this->newContent = $newContent;
    }

    public function getOldContent(): string
    {
        return $this->oldContent;
    }

    public function getNewContent(): string
    {
        return $this->newContent;
    }
}
