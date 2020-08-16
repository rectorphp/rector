<?php

declare(strict_types=1);

namespace Rector\Renaming\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Property;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use Rector\Core\Contract\Rector\ConfigurableRectorInterface;
use Rector\Core\Rector\AbstractPHPUnitRector;
use Rector\Core\RectorDefinition\ConfiguredCodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;
use Rector\NodeTypeResolver\Node\AttributeKey;

/**
 * @see \Rector\Renaming\Tests\Rector\ClassMethod\RenameAnnotationRector\RenameAnnotationRectorTest
 */
final class RenameAnnotationRector extends AbstractPHPUnitRector implements ConfigurableRectorInterface
{
    /**
     * @var string
     */
    public const CLASS_TO_ANNOTATION_MAP = '$classToAnnotationMap';

    /**
     * @var string[][]
     */
    private $classToAnnotationMap = [];

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
                        self::CLASS_TO_ANNOTATION_MAP => [
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
        /** @var Class_ $classLike */
        $classLike = $node->getAttribute(AttributeKey::CLASS_NODE);

        /** @var PhpDocInfo|null $phpDocInfo */
        $phpDocInfo = $node->getAttribute(AttributeKey::PHP_DOC_INFO);
        if ($phpDocInfo === null) {
            return null;
        }

        foreach ($this->classToAnnotationMap as $type => $annotationMap) {
            /** @var string $type */
            if (! $this->isObjectType($classLike, $type)) {
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

    public function configure(array $configuration): void
    {
        $this->classToAnnotationMap = $configuration[self::CLASS_TO_ANNOTATION_MAP] ?? [];
    }
}
