<?php

declare (strict_types=1);
namespace RectorPrefix202609\TomasVotruba\ClassLeak\ValueObject;

final class UnusedClassesResult
{
    /**
     * @var FileWithClass[]
     * @readonly
     */
    private array $parentLessFileWithClasses;
    /**
     * @var FileWithClass[]
     * @readonly
     */
    private array $withParentsFileWithClasses;
    /**
     * @var FileWithClass[]
     * @readonly
     */
    private array $traits;
    /**
     * @param FileWithClass[] $withParentsFileWithClasses
     * @param FileWithClass[] $parentLessFileWithClasses
     * @param FileWithClass[] $traits
     */
    public function __construct(array $parentLessFileWithClasses, array $withParentsFileWithClasses, array $traits)
    {
        $this->parentLessFileWithClasses = $parentLessFileWithClasses;
        $this->withParentsFileWithClasses = $withParentsFileWithClasses;
        $this->traits = $traits;
    }
    /**
     * @return FileWithClass[]
     */
    public function getParentLessFileWithClasses(): array
    {
        return $this->parentLessFileWithClasses;
    }
    /**
     * @return FileWithClass[]
     */
    public function getWithParentsFileWithClasses(): array
    {
        return $this->withParentsFileWithClasses;
    }
    public function getCount(): int
    {
        return count($this->parentLessFileWithClasses) + count($this->withParentsFileWithClasses) + count($this->traits);
    }
    /**
     * @return FileWithClass[]
     */
    public function getTraits(): array
    {
        return $this->traits;
    }
}
