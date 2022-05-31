<?php

declare(strict_types=1);

namespace Rector\Tests\PhpAttribute;

use Iterator;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\Use_;
use PhpParser\Node\Stmt\UseUse;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\Php80\ValueObject\AnnotationToAttribute;
use Rector\PhpAttribute\UseAliasNameMatcher;
use Rector\PhpAttribute\ValueObject\UseAliasMetadata;
use Rector\Testing\PHPUnit\AbstractTestCase;
use Rector\Tests\Php80\Rector\Class_\AnnotationToAttributeRector\Source\Annotation\OpenApi\Annotation\NestedPastAnnotation;
use Rector\Tests\Php80\Rector\Class_\AnnotationToAttributeRector\Source\Annotation\OpenApi\PastAnnotation;
use Rector\Tests\Php80\Rector\Class_\AnnotationToAttributeRector\Source\Attribute\OpenApi\Attribute\NestedFutureAttribute;
use Rector\Tests\Php80\Rector\Class_\AnnotationToAttributeRector\Source\Attribute\OpenApi\FutureAttribute;

final class UseAliasNameMatcherTest extends AbstractTestCase
{
    private UseAliasNameMatcher $useAliasNameMatcher;

    protected function setUp(): void
    {
        $this->boot();
        $this->useAliasNameMatcher = $this->getService(UseAliasNameMatcher::class);
    }

    /**
     * @dataProvider provideData()
     */
    public function test(
        AnnotationToAttribute $annotationToAttribute,
        string $useImportName,
        string $useAlias,
        string $shortAnnotationName,
        // attribute
        string $expectedAttributeUseImportName,
        string $expectedShortAttributeName,
    ): void {
        $useUse = new UseUse(new Name($useImportName), $useAlias);
        $useUse->setAttribute(AttributeKey::ORIGINAL_NODE, $useUse);

        $uses = [new Use_([$useUse, $useAlias])];

        // uses
        $useAliasMetadata = $this->useAliasNameMatcher->match($uses, $shortAnnotationName, $annotationToAttribute);

        $this->assertInstanceOf(UseAliasMetadata::class, $useAliasMetadata);

        // test new use import
        $this->assertSame($expectedShortAttributeName, $useAliasMetadata->getShortAttributeName());

        // test new short attribute name
        $this->assertSame($expectedAttributeUseImportName, $useAliasMetadata->getUseImportName());
    }

    public function provideData(): Iterator
    {
        yield [
            // configuration
            new AnnotationToAttribute(PastAnnotation::class, FutureAttribute::class),

            // use import
            'Rector\Tests\Php80\Rector\Class_\AnnotationToAttributeRector\Source\Annotation\OpenApi',
            // use import alias
            'OA',
            // short attribute name
            '@OA\PastAnnotation',

            // expected attribute import
            'Rector\Tests\Php80\Rector\Class_\AnnotationToAttributeRector\Source\Attribute\OpenApi',
            // expected attribute short name
            'OA\FutureAttribute',
        ];

        yield [
            // configuration
            new AnnotationToAttribute(NestedPastAnnotation::class, NestedFutureAttribute::class),

            // use import
            'Rector\Tests\Php80\Rector\Class_\AnnotationToAttributeRector\Source\Annotation\OpenApi\Annotation',
            // use import alias
            'OA',
            // short attribute name
            '@OA\NestedPastAnnotation',

            // expected attribute import
            'Rector\Tests\Php80\Rector\Class_\AnnotationToAttributeRector\Source\Attribute\OpenApi\Attribute',
            // expected attribute short name
            'OA\NestedFutureAttribute',
        ];
    }
}
