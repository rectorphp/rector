<?php

namespace Rector\Tests\TypeDeclaration\Rector\ClassMethod\AddArrayReturnDocTypeRector\Fixture;

use Doctrine\Common\Annotations\AnnotationException;
use stdClass;

final class SkipNonArrayVoid
{
    /**
     * @return stdClass|null
     */
    public function readClassAnnotation()
    {
        try {
            return new stdClass();
        } catch (AnnotationException $annotationException) {
            // unable to load
            return null;
        }
    }
}
