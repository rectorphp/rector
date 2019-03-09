<?php declare(strict_types=1);

namespace Rector\Rector\Annotation;

use PhpParser\Node;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Property;
use Rector\Exception\ShouldNotHappenException;
use Rector\NodeTypeResolver\Node\Attribute;
use Rector\NodeTypeResolver\PhpDoc\NodeAnalyzer\DocBlockManipulator;
use Rector\Rector\AbstractPHPUnitRector;
use Rector\RectorDefinition\ConfiguredCodeSample;
use Rector\RectorDefinition\RectorDefinition;

final class RenameAnnotationRector extends AbstractPHPUnitRector
{
    /**
     * @var string[][]
     */
    private $classToAnnotationMap = [];

    /**
     * @var string[]
     */
    private $activeAnnotationMap = [];

    /**
     * @var DocBlockManipulator
     */
    private $docBlockManipulator;

    /**
     * @param string[][] $classToAnnotationMap
     */
    public function __construct(array $classToAnnotationMap, DocBlockManipulator $docBlockManipulator)
    {
        $this->docBlockManipulator = $docBlockManipulator;
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
                        'PHPUnit\Framework\TestCase' => [
                            'test' => 'scenario',
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

        $parentNode = $node->getAttribute(Attribute::PARENT_NODE);
        if ($parentNode === null) {
            throw new ShouldNotHappenException();
        }

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
            $this->docBlockManipulator->replaceAnnotationInNode($node, $oldAnnotation, $newAnnotation);
        }

        return $node;
    }

    private function shouldSkip(Node $node): bool
    {
        if (! $node instanceof ClassMethod && ! $node instanceof Property) {
            return true;
        }

        return $node->getAttribute(Attribute::PARENT_NODE) === null;
    }

    private function hasAnyAnnotation(Node $node): bool
    {
        foreach (array_keys($this->activeAnnotationMap) as $oldAnnotation) {
            if ($this->docBlockManipulator->hasTag($node, $oldAnnotation)) {
                return true;
            }
        }

        return false;
    }
}
