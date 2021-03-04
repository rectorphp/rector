<?php

declare(strict_types=1);

namespace Rector\Utils\DoctrineAnnotationParserSyncer\Rector\StaticCall;

use PhpParser\Node;
use PhpParser\Node\Expr\StaticCall;
use PHPStan\Type\ObjectType;
use Rector\Core\Rector\AbstractRector;
use Rector\Utils\DoctrineAnnotationParserSyncer\Contract\Rector\ClassSyncerRectorInterface;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

final class RemoveAnnotationRegistryRegisterFileRector extends AbstractRector implements ClassSyncerRectorInterface
{
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Remove registerFile() static calls from AnnotationParser', [
            new CodeSample(
                <<<'CODE_SAMPLE'
class AnnotationParser
{
    public function run()
    {
        Doctrine\Common\Annotations\AnnotationRegistry::registerFile('...');
    }
}
CODE_SAMPLE
                ,
                <<<'CODE_SAMPLE'
class AnnotationParser
{
    public function run()
    {
    }
}
CODE_SAMPLE
            ),
        ]);
    }

    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [StaticCall::class];
    }

    /**
     * @param StaticCall $node
     */
    public function refactor(Node $node): ?Node
    {
        $desiredObjectTypes = [
            new ObjectType('Doctrine\Common\Annotations\DocParser'),
            new ObjectType('Doctrine\Common\Annotations\AnnotationReader'),
        ];

        if (! $this->nodeNameResolver->isInClassNames($node, $desiredObjectTypes)) {
            return null;
        }

        if (! $this->nodeNameResolver->isStaticCallNamed(
            $node,
            'Doctrine\Common\Annotations\AnnotationRegistry',
            'registerFile'
        )) {
            return null;
        }

        $this->removeNode($node);

        return $node;
    }
}
