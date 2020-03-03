<?php

declare(strict_types=1);

namespace Rector\BetterPhpDocParser\AnnotationReader;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\AnnotationRegistry;
use Doctrine\Common\Annotations\Reader;

final class AnnotationReaderFactory
{
    /**
     * @var string[]
     */
    private const IGNORED_NAMES = [
        'ORM\GeneratedValue',
        'GeneratedValue',
        'ORM\InheritanceType',
        'InheritanceType',
        'ORM\OrderBy',
        'OrderBy',
        'ORM\DiscriminatorMap',
        'DiscriminatorMap',
        'ORM\UniqueEntity',
        'UniqueEntity',
        'Gedmo\SoftDeleteable',
        'SoftDeleteable',
        'Gedmo\Slug',
        'Slug',
        'Gedmo\SoftDeleteable',
        'SoftDeleteable',
        'Gedmo\Blameable',
        'Blameable',
        'Gedmo\Versioned',
        'Versioned',
        // nette @inject dummy annotation
        'inject',
    ];

    public function create(): Reader
    {
        AnnotationRegistry::registerLoader('class_exists');

        $annotationReader = new AnnotationReader();

        // without this the reader will try to resolve them and fails with an exception
        // don't forget to add it to "stubs/Doctrine/Empty" directory, because the class needs to exists
        // and run "composer dump-autoload", because the directory is loaded by classmap
        foreach (self::IGNORED_NAMES as $ignoredName) {
            $annotationReader::addGlobalIgnoredName($ignoredName);
        }

        // warning: nested tags must be parse-able, e.g. @ORM\Table must include @ORM\UniqueConstraint!

        return $annotationReader;
    }
}
