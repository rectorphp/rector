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
    private $classToAnnotationMap;

    /**
     * @var DocBlockAnalyzer
     */
    private $docBlockAnalyzer;

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
        if (! $node instanceof ClassMethod && ! $node instanceof Property) {
            return false;
        }

        /** @var Node|null $parentNode */
        $parentNode = $node->getAttribute(Attribute::PARENT_NODE);
        if (! $parentNode) {
            return false;
        }

        foreach ($this->classToAnnotationMap as $type => $annotationMap) {
            if (! in_array($type, $parentNode->getAttribute(Attribute::TYPES), true)) {
                continue;
            }

            return $this->docBlockAnalyzer->hasAnnotation($node, 'scenario');
        }

        return false;
    }

    /**
     * @param ClassMethod|Property $classMethodOrPropertyNode
     */
    public function refactor(Node $classMethodOrPropertyNode): ?Node
    {
        $this->docBlockAnalyzer->replaceAnnotationInNode($classMethodOrPropertyNode, 'scenario', 'test');

        return $classMethodOrPropertyNode;
    }
}
