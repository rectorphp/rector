<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Rector\Renaming\ValueObject;

use RectorPrefix20220606\Rector\Renaming\Contract\RenameAnnotationInterface;
/**
 * @api
 */
final class RenameAnnotation implements RenameAnnotationInterface
{
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
    public function __construct(string $oldAnnotation, string $newAnnotation)
    {
        $this->oldAnnotation = $oldAnnotation;
        $this->newAnnotation = $newAnnotation;
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
