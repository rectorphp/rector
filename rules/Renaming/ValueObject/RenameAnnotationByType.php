<?php

declare (strict_types=1);
namespace Rector\Renaming\ValueObject;

use PHPStan\Type\ObjectType;
use Rector\Renaming\Contract\RenameAnnotationInterface;
use Rector\Validation\RectorAssert;
final class RenameAnnotationByType implements RenameAnnotationInterface
{
    /**
     * @readonly
     */
    private string $type;
    /**
     * @readonly
     */
    private string $oldAnnotation;
    /**
     * @readonly
     */
    private string $newAnnotation;
    public function __construct(string $type, string $oldAnnotation, string $newAnnotation)
    {
        $this->type = $type;
        $this->oldAnnotation = $oldAnnotation;
        $this->newAnnotation = $newAnnotation;
        RectorAssert::className($type);
    }
    public function getObjectType() : ObjectType
    {
        return new ObjectType($this->type);
    }
    public function getOldAnnotation() : string
    {
        return $this->oldAnnotation;
    }
    public function getNewAnnotation() : string
    {
        return $this->newAnnotation;
    }
}
