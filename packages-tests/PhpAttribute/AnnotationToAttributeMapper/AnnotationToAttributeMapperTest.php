<?php

declare(strict_types=1);

namespace Rector\Tests\PhpAttribute\AnnotationToAttributeMapper;

use PhpParser\Node\Scalar\String_;
use Rector\PhpAttribute\AnnotationToAttributeMapper;
use Rector\Testing\PHPUnit\AbstractTestCase;

final class AnnotationToAttributeMapperTest extends AbstractTestCase
{
    private AnnotationToAttributeMapper $annotationToAttributeMapper;

    protected function setUp(): void
    {
        $this->boot();
        $this->annotationToAttributeMapper = $this->getService(AnnotationToAttributeMapper::class);
    }

    public function test(): void
    {
        $mappedExpr = $this->annotationToAttributeMapper->map('hey');
        $this->assertInstanceOf(String_::class, $mappedExpr);
    }
}
