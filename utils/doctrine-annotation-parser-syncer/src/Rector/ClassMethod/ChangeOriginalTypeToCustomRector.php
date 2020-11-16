<?php

declare(strict_types=1);

namespace Rector\Utils\DoctrineAnnotationParserSyncer\Rector\ClassMethod;

use Doctrine\Common\Annotations\AnnotationReader;
use PhpParser\Node;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Stmt\ClassMethod;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\ValueObject\MethodName;
use Rector\DoctrineAnnotationGenerated\ConstantPreservingDocParser;
use Rector\Utils\DoctrineAnnotationParserSyncer\Contract\Rector\ClassSyncerRectorInterface;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

final class ChangeOriginalTypeToCustomRector extends AbstractRector implements ClassSyncerRectorInterface
{
    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [ClassMethod::class];
    }

    /**
     * @param ClassMethod $node
     */
    public function refactor(Node $node): ?Node
    {
        if (! $this->isInClassNamed($node, AnnotationReader::class)) {
            return null;
        }

        if (! $this->isName($node, MethodName::CONSTRUCT)) {
            return null;
        }

        $firstParam = $node->params[0];
        $firstParam->type = new FullyQualified(ConstantPreservingDocParser::class);

        return $node;
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Change DocParser type to custom one', [
            new CodeSample(
                <<<'CODE_SAMPLE'
namespace Doctrine\Common\Annotations;

class AnnotationReader
{
    public function __construct(... $parser)
    {
    }
}
CODE_SAMPLE
                ,
                <<<'CODE_SAMPLE'
namespace Doctrine\Common\Annotations;

class AnnotationReader
{
    public function __construct(\Rector\DoctrineAnnotationGenerated\ConstantPreservingDocParser $parser)
    {
    }
}
CODE_SAMPLE
            ),
        ]);
    }
}
