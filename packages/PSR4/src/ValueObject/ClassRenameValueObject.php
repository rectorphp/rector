<?php declare(strict_types=1);

namespace Rector\PSR4\ValueObject;

final class ClassRenameValueObject
{
    /**
     * @var string
     */
    private $oldClass;

    /**
     * @var string
     */
    private $newClass;

    /**
     * @var string[]
     */
    private $parentClasses = [];

    /**
     * @param string[] $parentClasses
     */
    public function __construct(string $oldClass, string $newClass, array $parentClasses = [])
    {
        $this->oldClass = $oldClass;
        $this->newClass = $newClass;
        $this->parentClasses = $parentClasses;
    }

    public function getOldClass(): string
    {
        return $this->oldClass;
    }

    public function getNewClass(): string
    {
        return $this->newClass;
    }

    /**
     * @return string[]
     */
    public function getParentClasses(): array
    {
        return $this->parentClasses;
    }

    public function removeParent(string $class): void
    {
        foreach ($this->parentClasses as $key => $parentClass) {
            if ($parentClass !== $class) {
                continue;
            }

            unset($this->parentClasses[$key]);
            break;
        }
    }
}
