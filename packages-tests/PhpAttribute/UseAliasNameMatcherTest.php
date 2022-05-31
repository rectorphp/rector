<?php

declare(strict_types=1);

namespace Rector\Tests\PhpAttribute;

use PhpParser\Node\Name;
use PhpParser\Node\Stmt\Use_;
use PhpParser\Node\Stmt\UseUse;
use Rector\Php80\ValueObject\AnnotationToAttribute;
use Rector\PhpAttribute\UseAliasNameMatcher;
use Rector\PhpAttribute\ValueObject\UseAliasMetadata;
use Rector\Testing\PHPUnit\AbstractTestCase;
use Rector\Tests\Php80\Rector\Class_\AnnotationToAttributeRector\Source\Annotation\OpenApi\PastAnnotation;
use Rector\Tests\Php80\Rector\Class_\AnnotationToAttributeRector\Source\Attribute\OpenApi\FutureAttribute;

final class UseAliasNameMatcherTest extends AbstractTestCase
{
    /**
     * @var string
     */
    private const USE_IMPORT_NAME = 'Rector\Tests\Php80\Rector\Class_\AnnotationToAttributeRector\Source\Annotation\OpenApi';

    /**
     * @var string
     */
    private const USE_ALIAS = 'OA';

    private UseAliasNameMatcher $useAliasNameMatcher;

    protected function setUp(): void
    {
        $this->boot();
        $this->useAliasNameMatcher = $this->getService(UseAliasNameMatcher::class);
    }

    public function test(): void
    {
        $annotationToAttribute = new AnnotationToAttribute(PastAnnotation::class, FutureAttribute::class);

        $uses = [new Use_([new UseUse(new Name(self::USE_IMPORT_NAME), self::USE_ALIAS), self::USE_ALIAS])];

        // uses
        $useAliasMetadata = $this->useAliasNameMatcher->match($uses, '@OA\PastAnnotation', $annotationToAttribute);

        $this->assertInstanceOf(UseAliasMetadata::class, $useAliasMetadata);

        // test new use import
        $this->assertSame('OA\FutureAttribute', $useAliasMetadata->getShortAttributeName());

        // test new short attribute name
        $this->assertSame(
            'Rector\Tests\Php80\Rector\Class_\AnnotationToAttributeRector\Source\Attribute\OpenApi',
            $useAliasMetadata->getUseImportName()
        );
    }
}
