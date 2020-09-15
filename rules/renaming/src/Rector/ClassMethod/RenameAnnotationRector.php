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
use Rector\Renaming\ValueObject\RenameAnnotation;
use Webmozart\Assert\Assert;

/**
 * @see \Rector\Renaming\Tests\Rector\ClassMethod\RenameAnnotationRector\RenameAnnotationRectorTest
 */
final class RenameAnnotationRector extends AbstractPHPUnitRector implements ConfigurableRectorInterface
{
    /**
     * @var string
     */
    public const RENAMED_ANNOTATIONS_IN_TYPES = 'renamed_annotations_in_types';

    /**
     * @var RenameAnnotation[]
     */
    private $renamedAnnotationInTypes = [];

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
                        self::RENAMED_ANNOTATIONS_IN_TYPES => [
                            new RenameAnnotation('PHPUnit\Framework\TestCase', 'test', 'scenario'),
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

        foreach ($this->renamedAnnotationInTypes as $renamedAnnotationInType) {
            if (! $this->isObjectType($classLike, $renamedAnnotationInType->getType())) {
                continue;
            }

            if (! $phpDocInfo->hasByName($renamedAnnotationInType->getOldAnnotation())) {
                continue;
            }

            $this->docBlockManipulator->replaceAnnotationInNode($node, $renamedAnnotationInType);
        }

        return $node;
    }

    public function configure(array $configuration): void
    {
        $renamedAnnotationsInTypes = $configuration[self::RENAMED_ANNOTATIONS_IN_TYPES] ?? [];
        Assert::allIsInstanceOf($renamedAnnotationsInTypes, RenameAnnotation::class);
        $this->renamedAnnotationInTypes = $renamedAnnotationsInTypes;
    }
}
