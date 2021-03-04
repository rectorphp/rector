<?php

declare(strict_types=1);

namespace Rector\Utils\DoctrineAnnotationParserSyncer\Rector\Assign;

use PhpParser\Node;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Name\FullyQualified;
use PHPStan\Type\ObjectType;
use Rector\Core\Rector\AbstractRector;
use Rector\Utils\DoctrineAnnotationParserSyncer\Contract\Rector\ClassSyncerRectorInterface;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

final class AssignNewDocParserRector extends AbstractRector implements ClassSyncerRectorInterface
{
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [Assign::class];
    }

    /**
     * @param Assign $node
     */
    public function refactor(Node $node): ?Node
    {
        if (! $this->nodeNameResolver->isInClassNamed(
            $node,
            new ObjectType('Doctrine\Common\Annotations\AnnotationReader')
        )) {
            return null;
        }

        if (! $this->isName($node->var, 'preParser')) {
            return null;
        }

        $node->expr = new New_(new FullyQualified('Rector\DoctrineAnnotationGenerated\ConstantPreservingDocParser'));

        return $node;
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Change $this->preParser assign to new doc parser', [
            new CodeSample(
                <<<'CODE_SAMPLE'
namespace Doctrine\Common\Annotations;

class AnnotationReader
{
    public function run()
    {
        $this->preParser = ...
    }
}
CODE_SAMPLE
                ,
                <<<'CODE_SAMPLE'
namespace Doctrine\Common\Annotations;

class AnnotationReader
{
    public function run()
    {
        $this->preParser = new \Rector\DoctrineAnnotationGenerated\ConstantPreservingDocParser();
    }
}
CODE_SAMPLE
            ),
        ]);
    }
}
