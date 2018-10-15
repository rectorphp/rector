<?php declare(strict_types=1);

namespace Rector\Rector\Annotation;

use PhpParser\Node;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Property;
use Rector\NodeTypeResolver\Node\Attribute;
use Rector\NodeTypeResolver\PhpDoc\NodeAnalyzer\DocBlockAnalyzer;
use Rector\Rector\AbstractPHPUnitRector;
use Rector\RectorDefinition\ConfiguredCodeSample;
use Rector\RectorDefinition\RectorDefinition;

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

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition(
            'Turns defined annotations above properties and methods to their new values.',
            [
                new ConfiguredCodeSample(
                    <<<'CODE_SAMPLE'
class SomeTest extends PHPUnit\Framework\TestCase
{
    /**
     * @test
     */
    public function someMethod()
    {
    }
}
CODE_SAMPLE
                    ,
                    <<<'CODE_SAMPLE'
class SomeTest extends PHPUnit\Framework\TestCase
{
    /** 
     * @scenario
     */
    public function someMethod()
    {
    }
}
CODE_SAMPLE
                    ,
                    [
                        '$classToAnnotationMap' => [
                            'PHPUnit\Framework\TestCase' => [
                                'test' => 'scenario',
                            ],
                        ],
                    ]
                ),
            ]
        );
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [ClassMethod::class, Property::class];
    }

    /**
     * @param ClassMethod|Property $node
     */
    public function refactor(Node $node): ?Node
    {
        if ($this->shouldSkip($node)) {
            return null;
        }

        /** @var Node $parentNode */
        $parentNode = $node->getAttribute(Attribute::PARENT_NODE);
        $parentNodeTypes = $this->getTypes($parentNode);
        foreach ($this->classToAnnotationMap as $type => $annotationMap) {
            if (! in_array($type, $parentNodeTypes, true)) {
                continue;
            }

            $this->activeAnnotationMap = $annotationMap;

            if (! $this->hasAnyAnnotation($node)) {
                return null;
            }
        }

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
        if (! $parentNode) {
            return true;
        }

        return false;
    }

    private function hasAnyAnnotation(Node $node): bool
    {
        foreach (array_keys($this->activeAnnotationMap) as $oldAnnotation) {
            if ($this->docBlockAnalyzer->hasTag($node, $oldAnnotation)) {
                return true;
            }
        }

        return false;
    }
}
