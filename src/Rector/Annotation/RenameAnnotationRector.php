<?php declare(strict_types=1);

namespace Rector\Rector\Annotation;

use PhpParser\Node\Stmt\Class_;
use PhpParser\Node;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Property;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\NodeTypeResolver\PhpDoc\NodeAnalyzer\DocBlockManipulator;
use Rector\Rector\AbstractPHPUnitRector;
use Rector\RectorDefinition\ConfiguredCodeSample;
use Rector\RectorDefinition\RectorDefinition;

/**
 * @see \Rector\Tests\Rector\Annotation\RenameAnnotationRector\RenameAnnotationRectorTest
 */
final class RenameAnnotationRector extends AbstractPHPUnitRector
{
    /**
     * @var string[][]
     */
    private $classToAnnotationMap = [];

    /**
     * @var DocBlockManipulator
     */
    private $docBlockManipulator;

    /**
     * @param string[][] $classToAnnotationMap
     */
    public function __construct(DocBlockManipulator $docBlockManipulator, array $classToAnnotationMap = [])
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
        /** @var Class_ $class */
        $class = $node->getAttribute(AttributeKey::CLASS_NODE);

        foreach ($this->classToAnnotationMap as $type => $annotationMap) {
            /** @var string $type */
            if (! $this->isObjectType($class, $type)) {
                continue;
            }

            foreach ($annotationMap as $oldAnnotation => $newAnnotation) {
                if (! $this->docBlockManipulator->hasTag($node, $oldAnnotation)) {
                    continue;
                }

                $this->docBlockManipulator->replaceAnnotationInNode($node, $oldAnnotation, $newAnnotation);
            }
        }

        return $node;
    }
}
