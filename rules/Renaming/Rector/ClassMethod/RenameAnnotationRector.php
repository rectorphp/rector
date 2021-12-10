<?php

declare(strict_types=1);

namespace Rector\Renaming\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\Property;
use Rector\Core\Contract\Rector\ConfigurableRectorInterface;
use Rector\Core\Rector\AbstractRector;
use Rector\NodeTypeResolver\PhpDoc\NodeAnalyzer\DocBlockTagReplacer;
use Rector\Renaming\Contract\RenameAnnotationInterface;
use Rector\Renaming\ValueObject\RenameAnnotationByType;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
use Webmozart\Assert\Assert;

/**
 * @see \Rector\Tests\Renaming\Rector\ClassMethod\RenameAnnotationRector\RenameAnnotationRectorTest
 */
final class RenameAnnotationRector extends AbstractRector implements ConfigurableRectorInterface
{
    /**
     * @deprecated
     * @var string
     */
    final public const RENAMED_ANNOTATIONS = 'renamed_annotations';

    /**
     * @var RenameAnnotationInterface[]
     */
    private array $renameAnnotations = [];

    public function __construct(
        private readonly DocBlockTagReplacer $docBlockTagReplacer
    ) {
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Turns defined annotations above properties and methods to their new values.',
            [
                new ConfiguredCodeSample(
                    <<<'CODE_SAMPLE'
use PHPUnit\Framework\TestCase;

final class SomeTest extends TestCase
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
use PHPUnit\Framework\TestCase;

final class SomeTest extends TestCase
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
                    [new RenameAnnotationByType('PHPUnit\Framework\TestCase', 'test', 'scenario')]
                ),
            ]
        );
    }

    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [ClassMethod::class, Property::class, Expression::class];
    }

    /**
     * @param ClassMethod|Property $node
     */
    public function refactor(Node $node): ?Node
    {
        $classLike = $this->betterNodeFinder->findParentType($node, Class_::class);
        if (! $classLike instanceof Class_) {
            return null;
        }

        $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($node);

        foreach ($this->renameAnnotations as $renameAnnotation) {
            if ($renameAnnotation instanceof RenameAnnotationByType && ! $this->isObjectType(
                $classLike,
                $renameAnnotation->getObjectType()
            )) {
                continue;
            }

            $this->docBlockTagReplacer->replaceTagByAnother(
                $phpDocInfo,
                $renameAnnotation->getOldAnnotation(),
                $renameAnnotation->getNewAnnotation()
            );
        }

        return $node;
    }

    /**
     * @param mixed[] $configuration
     */
    public function configure(array $configuration): void
    {
        $renamedAnnotations = $configuration[self::RENAMED_ANNOTATIONS] ?? $configuration;

        Assert::isArray($renamedAnnotations);
        Assert::allIsAOf($renamedAnnotations, RenameAnnotationInterface::class);

        $this->renameAnnotations = $renamedAnnotations;
    }
}
