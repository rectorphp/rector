<?php declare(strict_types=1);

namespace Rector\Rector\Dynamic;

use PhpParser\Node;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Property;
use Rector\Node\Attribute;
use Rector\Rector\AbstractPHPUnitRector;
use Rector\ReflectionDocBlock\NodeAnalyzer\DocBlockAnalyzer;

/**
 * Before:
 * - @scenario
 *
 * After:
 * - @test
 */
final class AnnotationReplacerRector extends AbstractPHPUnitRector
{
    /**
     * @var string[][]
     */
    private $classToAnnotationMap = [];

    /**
     * @var DocBlockAnalyzer
     */
    private $docBlockAnalyzer;

    /**
     * @var string[]
     */
    private $activeAnnotationMap = [];

    /**
     * @param string[][] $classToAnnotationMap
     */
    public function __construct(array $classToAnnotationMap, DocBlockAnalyzer $docBlockAnalyzer)
    {
        $this->docBlockAnalyzer = $docBlockAnalyzer;
        $this->classToAnnotationMap = $classToAnnotationMap;
    }

    public function isCandidate(Node $node): bool
    {
        if ($this->shouldSkip($node)) {
            return false;
        }

        $parentNode = $node->getAttribute(Attribute::PARENT_NODE);
        foreach ($this->classToAnnotationMap as $type => $annotationMap) {
            if (! in_array($type, $parentNode->getAttribute(Attribute::TYPES), true)) {
                continue;
            }

            $this->activeAnnotationMap = $annotationMap;

            if ($this->hasAnyAnnotation($node)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param ClassMethod|Property $node
     */
    public function refactor(Node $node): ?Node
    {
        foreach ($this->activeAnnotationMap as $oldAnnotation => $newAnnotation) {
            $this->docBlockAnalyzer->replaceAnnotationInNode($node, $oldAnnotation, $newAnnotation);
        }

        return $node;
    }

    private function shouldSkip(Node $node): bool
    {
        if (! $node instanceof ClassMethod && ! $node instanceof Property) {
            return true;
        }

        /** @var Node|null $parentNode */
        $parentNode = $node->getAttribute(Attribute::PARENT_NODE);

        return $parentNode === null;
    }

    private function hasAnyAnnotation(Node $node): bool
    {
        foreach ($this->activeAnnotationMap as $oldAnnotation => $newAnnotation) {
            if ($this->docBlockAnalyzer->hasAnnotation($node, $oldAnnotation)) {
                return true;
            }
        }

        return false;
    }
}
