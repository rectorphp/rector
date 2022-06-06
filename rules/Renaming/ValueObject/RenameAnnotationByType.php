<?php

declare (strict_types=1);
namespace Rector\Renaming\ValueObject;

use PHPStan\Type\ObjectType;
use Rector\Core\Validation\RectorAssert;
use Rector\Renaming\Contract\RenameAnnotationInterface;
final class RenameAnnotationByType implements \Rector\Renaming\Contract\RenameAnnotationInterface
{
    /**
     * @readonly
     * @var string
     */
    private $type;
    /**
     * @readonly
     * @var string
     */
    private $oldAnnotation;
    /**
     * @readonly
     * @var string
     */
    private $newAnnotation;
    public function __construct(string $type, string $oldAnnotation, string $newAnnotation)
    {
        $this->type = $type;
        $this->oldAnnotation = $oldAnnotation;
        $this->newAnnotation = $newAnnotation;
        \Rector\Core\Validation\RectorAssert::className($type);
    }
    public function getObjectType() : \PHPStan\Type\ObjectType
    {
        return new \PHPStan\Type\ObjectType($this->type);
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
