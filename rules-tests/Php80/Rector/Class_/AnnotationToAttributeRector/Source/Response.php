<?php

declare(strict_types=1);

namespace Rector\Tests\Php80\Rector\Class_\AnnotationToAttributeRector\Source;

/**
 * @annotation
 */
final class Response
{
    public function __construct(int $code, string $description, string $entity)
    {
    }
}
