<?php declare(strict_types=1);

namespace Rector\DoctrinePhpDocParser\AnnotationReader;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\AnnotationRegistry;

final class AnnotationReaderFactory
{
    public function create(): AnnotationReader
    {
        AnnotationRegistry::registerLoader('class_exists');

        return new AnnotationReader();
    }
}
