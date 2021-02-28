<?php

declare(strict_types=1);

namespace Rector\Utils\DoctrineAnnotationParserSyncer\Rector\StaticCall;

use Doctrine\Common\Annotations\AnnotationRegistry;
use PhpParser\Node;
use PhpParser\Node\Expr\StaticCall;
use PHPStan\Type\ObjectType;
use Rector\Core\Rector\AbstractTemporaryRector;
use Rector\Utils\DoctrineAnnotationParserSyncer\Contract\Rector\ClassSyncerRectorInterface;

final class RemoveAnnotationRegistryRegisterFileRector extends AbstractTemporaryRector implements ClassSyncerRectorInterface
{
    /**
     * @return array<class-string<\PhpParser\Node>>
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

        if (! $this->nodeNameResolver->isStaticCallNamed($node, AnnotationRegistry::class, 'registerFile')) {
            return null;
        }

        $this->removeNode($node);

        return $node;
    }
}
