<?php

declare(strict_types=1);

namespace Rector\Renaming\Rector\Annotation;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Property;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use Rector\Core\Rector\AbstractPHPUnitRector;
use Rector\Core\RectorDefinition\ConfiguredCodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;
use Rector\NodeTypeResolver\Node\AttributeKey;

/**
 * @see \Rector\Renaming\Tests\Rector\Annotation\RenameAnnotationRector\RenameAnnotationRectorTest
 */
final class RenameAnnotationRector extends AbstractPHPUnitRector
{
    /**
     * @var string[][]
     */
    private $classToAnnotationMap = [];

    /**
     * @param string[][] $classToAnnotationMap
     */
    public function __construct(array $classToAnnotationMap = [])
    {
        $this->classToAnnotationMap = $classToAnnotationMap;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition(
            'Turns defined annotations above properties and methods to their new values.',
            [
                new ConfiguredCodeSample(
                    <<<'PHP'
class SomeTest extends PHPUnit\Framework\TestCase
{
    /**
     * @test
     */
    public function someMethod()
    {
    }
}
PHP
                    ,
                    <<<'PHP'
class SomeTest extends PHPUnit\Framework\TestCase
{
    /**
     * @scenario
     */
    public function someMethod()
    {
    }
}
PHP
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

        /** @var PhpDocInfo $phpDocInfo */
        $phpDocInfo = $node->getAttribute(AttributeKey::PHP_DOC_INFO);

        foreach ($this->classToAnnotationMap as $type => $annotationMap) {
            /** @var string $type */
            if (! $this->isObjectType($class, $type)) {
                continue;
            }

            foreach ($annotationMap as $oldAnnotation => $newAnnotation) {
                if (! $phpDocInfo->hasByName($oldAnnotation)) {
                    continue;
                }

                $this->docBlockManipulator->replaceAnnotationInNode($node, $oldAnnotation, $newAnnotation);
            }
        }

        return $node;
    }
}
