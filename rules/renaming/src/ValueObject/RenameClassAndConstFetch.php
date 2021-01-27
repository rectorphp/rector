<?php

declare(strict_types=1);

namespace Rector\Renaming\ValueObject;

use Rector\Renaming\Contract\RenameClassConstFetchInterface;

final class RenameClassAndConstFetch implements RenameClassConstFetchInterface
{
    /**
     * @var string
     */
    private $oldClass;

    /**
     * @var string
     */
    private $oldConstant;

    /**
     * @var string
     */
    private $newConstant;

    /**
     * @var string
     */
    private $newClass;

    public function __construct(string $oldClass, string $oldConstant, string $newClass, string $newConstant)
    {
        $this->oldClass = $oldClass;
        $this->oldConstant = $oldConstant;
        $this->newConstant = $newConstant;
        $this->newClass = $newClass;
    }

    public function getOldClass(): string
    {
        return $this->oldClass;
    }

    public function getOldConstant(): string
    {
        return $this->oldConstant;
    }

    public function getNewConstant(): string
    {
        return $this->newConstant;
    }

    public function getNewClass(): string
    {
        return $this->newClass;
    }
}
