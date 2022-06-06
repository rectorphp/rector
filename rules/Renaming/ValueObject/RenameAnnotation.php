<?php

declare (strict_types=1);
namespace Rector\Renaming\ValueObject;

use Rector\Renaming\Contract\RenameAnnotationInterface;
/**
 * @api
 */
final class RenameAnnotation implements \Rector\Renaming\Contract\RenameAnnotationInterface
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
