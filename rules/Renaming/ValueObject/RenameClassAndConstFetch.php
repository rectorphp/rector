<?php

declare (strict_types=1);
namespace Rector\Renaming\ValueObject;

use PHPStan\Type\ObjectType;
use Rector\Core\Validation\RectorAssert;
use Rector\Renaming\Contract\RenameClassConstFetchInterface;
final class RenameClassAndConstFetch implements RenameClassConstFetchInterface
{
    /**
     * @var class-string
     * @readonly
     */
    private $oldClass;
    /**
     * @readonly
     * @var string
     */
    private $oldConstant;
    /**
     * @var class-string
     * @readonly
     */
    private $newClass;
    /**
     * @readonly
     * @var string
     */
    private $newConstant;
    /**
     * @param class-string $oldClass
     * @param class-string $newClass
     */
    public function __construct(string $oldClass, string $oldConstant, string $newClass, string $newConstant)
    {
        $this->oldClass = $oldClass;
        $this->oldConstant = $oldConstant;
        $this->newClass = $newClass;
        $this->newConstant = $newConstant;
        RectorAssert::className($oldClass);
        RectorAssert::constantName($oldConstant);
        RectorAssert::className($newClass);
        RectorAssert::constantName($newConstant);
    }
    public function getOldObjectType() : ObjectType
    {
        return new ObjectType($this->oldClass);
    }
    public function getOldConstant() : string
    {
        return $this->oldConstant;
    }
    public function getNewConstant() : string
    {
        return $this->newConstant;
    }
    public function getNewClass() : string
    {
        return $this->newClass;
    }
}
