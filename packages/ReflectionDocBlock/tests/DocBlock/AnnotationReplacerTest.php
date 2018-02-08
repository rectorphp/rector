<?php declare(strict_types=1);

namespace Rector\ReflectionDocBlock\Tests\DocBlock;

use Rector\ReflectionDocBlock\DocBlock\AnnotationReplacer;
use Rector\Tests\AbstractContainerAwareTestCase;

final class AnnotationReplacerTest extends AbstractContainerAwareTestCase
{
    /**
     * @var AnnotationReplacer
     */
    private $annotationReplacer;

    protected function setUp(): void
    {
        $this->annotationReplacer = $this->container->get(AnnotationReplacer::class);
    }

    public function test(): void
    {
        $oldContent = '/** @oldAnnotation */';
        $newContent = $this->annotationReplacer->replaceOldByNew($oldContent, 'oldAnnotation', 'newAnnotation');

        $this->assertSame('/** @newAnnotation */', $newContent);
    }
}
