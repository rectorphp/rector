<?php

declare(strict_types=1);

namespace Rector\Tests\PhpAttribute\AnnotationToAttributeMapper;

use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\ArrayItem;
use PhpParser\Node\Expr\ConstFetch;
use PhpParser\Node\Scalar\LNumber;
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
        $mappedExpr = $this->annotationToAttributeMapper->map(false);
        $this->assertInstanceOf(ConstFetch::class, $mappedExpr);

        $mappedExpr = $this->annotationToAttributeMapper->map('false');
        $this->assertInstanceOf(ConstFetch::class, $mappedExpr);

        $mappedExpr = $this->annotationToAttributeMapper->map('100');
        $this->assertInstanceOf(LNumber::class, $mappedExpr);

        $mappedExpr = $this->annotationToAttributeMapper->map('hey');
        $this->assertInstanceOf(String_::class, $mappedExpr);

        $expr = $this->annotationToAttributeMapper->map(['hey']);
        $this->assertInstanceOf(Array_::class, $expr);

        $arrayItem = $expr->items[0];
        $this->assertInstanceOf(ArrayItem::class, $arrayItem);
        $this->assertInstanceOf(String_::class, $arrayItem->value);
    }
}
