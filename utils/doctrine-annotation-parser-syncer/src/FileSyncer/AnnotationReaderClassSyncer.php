<?php

declare(strict_types=1);

namespace Rector\Utils\DoctrineAnnotationParserSyncer\FileSyncer;

final class AnnotationReaderClassSyncer extends AbstractClassSyncer
{
    public function getSourceFilePath(): string
    {
        return __DIR__ . '/../../../../vendor/doctrine/annotations/lib/Doctrine/Common/Annotations/AnnotationReader.php';
    }

    public function getTargetFilePath(): string
    {
        return __DIR__ . '/../../../../packages/doctrine-annotation-generated/src/ConstantPreservingAnnotationReader.php';
    }
}
