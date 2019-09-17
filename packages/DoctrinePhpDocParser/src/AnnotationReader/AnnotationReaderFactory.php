<?php declare(strict_types=1);

namespace Rector\DoctrinePhpDocParser\AnnotationReader;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\AnnotationRegistry;

final class AnnotationReaderFactory
{
    public function create(): AnnotationReader
    {
        AnnotationRegistry::registerLoader('class_exists');

        $annotationReader = new AnnotationReader();

        // without this the reader will try to resolve them and fails with an exception
        // don't forget to add it to "stubs/Doctrine/Empty" directory, because the class needs to exists
        // and run "composer dump-autoload", because the directory is loaded by classmap
        $annotationReader::addGlobalIgnoredName('ORM\GeneratedValue');
        $annotationReader::addGlobalIgnoredName('ORM\InheritanceType');
        $annotationReader::addGlobalIgnoredName('ORM\OrderBy');
        $annotationReader::addGlobalIgnoredName('ORM\DiscriminatorMap');
        $annotationReader::addGlobalIgnoredName('Gedmo\SoftDeleteable');

        // warning: nested tags must be parse-able, e.g. @ORM\Table must include @ORM\UniqueConstraint!

        return $annotationReader;
    }
}
