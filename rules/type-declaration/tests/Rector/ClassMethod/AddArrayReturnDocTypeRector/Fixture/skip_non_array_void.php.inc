<?php

namespace Rector\TypeDeclaration\Tests\Rector\ClassMethod\AddArrayReturnDocTypeRector\Fixture;

use Doctrine\Common\Annotations\AnnotationException;
use PhpParser\Node\Stmt\Class_;
use stdClass;

class SkipNonArrayVoid
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
