<?php

declare(strict_types=1);

namespace Rector\Utils\DoctrineAnnotationParserSyncer\Rector\StaticCall;

use PhpParser\Node;
use PhpParser\Node\Expr\StaticCall;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Type\ObjectType;
use Rector\Core\Rector\AbstractRector;
use Rector\NodeTypeResolver\Node\AttributeKey;
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
        $scope = $node->getAttribute(AttributeKey::SCOPE);
        if (! $scope instanceof Scope) {
            return null;
        }

        $classReflection = $scope->getClassReflection();
        if (! $classReflection instanceof ClassReflection) {
            return null;
        }

        if (! $classReflection->isSubclassOf(
            'Doctrine\Common\Annotations\DocParser'
        ) && ! $classReflection->isSubclassOf('Doctrine\Common\Annotations\AnnotationReader')) {
            return null;
        }

        $callerType = $this->nodeTypeResolver->resolve($node->class);
        if (! $callerType->isSuperTypeOf(new ObjectType('Doctrine\Common\Annotations\AnnotationRegistry'))->yes()) {
            return null;
        }

        if (! $this->isName($node->name, 'registerFile')) {
            return null;
        }

        $this->removeNode($node);

        return $node;
    }
}
