<?php

declare(strict_types=1);

namespace Rector\Generic\Rector\ClassLike;

use PhpParser\Node;
use PhpParser\Node\FunctionLike;
use PhpParser\Node\Stmt\ClassConst;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\Node\Stmt\Property;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use Rector\Core\Contract\Rector\ConfigurableRectorInterface;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\ConfiguredCodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;
use Rector\NodeTypeResolver\Node\AttributeKey;

/**
 * @see \Rector\Generic\Tests\Rector\ClassLike\RemoveAnnotationRector\RemoveAnnotationRectorTest
 */
final class RemoveAnnotationRector extends AbstractRector implements ConfigurableRectorInterface
{
    /**
     * @var string
     */
    public const ANNOTATIONS_TO_REMOVE = 'annotations_to_remove';

    /**
     * @var mixed[]
     */
    private $annotationsToRemove = [];

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Remove annotation by names', [
            new ConfiguredCodeSample(
                <<<'CODE_SAMPLE'
/**
 * @method getName()
 */
final class SomeClass
{
}
CODE_SAMPLE
,
                <<<'CODE_SAMPLE'
final class SomeClass
{
}
CODE_SAMPLE
,
                [
                    self::ANNOTATIONS_TO_REMOVE => ['method'],
                ]
            ),
        ]);
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [ClassLike::class, FunctionLike::class, Property::class, ClassConst::class];
    }

    /**
     * @param ClassLike|FunctionLike|Property|ClassConst $node
     */
    public function refactor(Node $node): ?Node
    {
        /** @var PhpDocInfo|null $phpDocInfo */
        $phpDocInfo = $node->getAttribute(AttributeKey::PHP_DOC_INFO);
        if ($phpDocInfo === null) {
            return null;
        }

        foreach ($this->annotationsToRemove as $annotationToRemove) {
            if (! $phpDocInfo->hasByName($annotationToRemove)) {
                continue;
            }

            $phpDocInfo->removeByName($annotationToRemove);
        }

        return $node;
    }

    /**
     * @param mixed[] $configuration
     */
    public function configure(array $configuration): void
    {
        $this->annotationsToRemove = $configuration[self::ANNOTATIONS_TO_REMOVE] ?? [];
    }
}
