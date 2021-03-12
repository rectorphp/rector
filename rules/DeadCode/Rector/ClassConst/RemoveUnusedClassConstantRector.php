<?php

declare(strict_types=1);

namespace Rector\DeadCode\Rector\ClassConst;

use PhpParser\Node;
use PhpParser\Node\Stmt\ClassConst;
use PhpParser\Node\Stmt\ClassLike;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ClassReflection;
use PHPStan\ShouldNotHappenException;
use Rector\BetterPhpDocParser\ValueObject\PhpDocNode\ApiPhpDocTagNode;
use Rector\Caching\Contract\Rector\ZeroCacheRectorInterface;
use Rector\Core\NodeManipulator\ClassConstManipulator;
use Rector\Core\Rector\AbstractRector;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @see \Rector\DeadCode\Tests\Rector\ClassConst\RemoveUnusedClassConstantRector\RemoveUnusedClassConstantRectorTest
 */
final class RemoveUnusedClassConstantRector extends AbstractRector implements ZeroCacheRectorInterface
{
    /**
     * @var ClassConstManipulator
     */
    private $classConstManipulator;

    public function __construct(ClassConstManipulator $classConstManipulator)
    {
        $this->classConstManipulator = $classConstManipulator;
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Remove unused class constants', [
            new CodeSample(
                <<<'CODE_SAMPLE'
class SomeClass
{
    private const SOME_CONST = 'dead';

    public function run()
    {
    }
}
CODE_SAMPLE
,
                <<<'CODE_SAMPLE'
class SomeClass
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
        return [ClassConst::class];
    }

    /**
     * @param ClassConst $node
     */
    public function refactor(Node $node): ?Node
    {
        if ($this->shouldSkip($node)) {
            return null;
        }

        /** @var Scope $scope */
        $scope = $node->getAttribute(AttributeKey::SCOPE);

        $classReflection = $scope->getClassReflection();
        if (! $classReflection instanceof ClassReflection) {
            throw new ShouldNotHappenException();
        }

        $nodeRepositoryFindInterface = $this->nodeRepository->findInterface($classReflection->getName());

        // 0. constants declared in interfaces have to be public
        if ($nodeRepositoryFindInterface !== null) {
            $this->visibilityManipulator->makePublic($node);
            return $node;
        }

        /** @var string $constant */
        $constant = $this->getName($node);

        $directUsingClassReflections = $this->nodeRepository->findDirectClassConstantFetches(
            $classReflection,
            $constant
        );

        $indirectUsingClassReflections = $this->nodeRepository->findIndirectClassConstantFetches(
            $classReflection,
            $constant
        );

        $usingClassReflections = array_merge($directUsingClassReflections, $indirectUsingClassReflections);
        if ($usingClassReflections !== []) {
            return null;
        }

        $this->removeNode($node);

        return null;
    }

    private function shouldSkip(ClassConst $classConst): bool
    {
        if ($this->isOpenSourceProjectType()) {
            return true;
        }

        if (count($classConst->consts) !== 1) {
            return true;
        }

        if ($this->classConstManipulator->isEnum($classConst)) {
            return true;
        }

        $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($classConst);
        if ($phpDocInfo->hasByType(ApiPhpDocTagNode::class)) {
            return true;
        }

        $classLike = $classConst->getAttribute(AttributeKey::CLASS_NODE);

        if ($classLike instanceof ClassLike) {
            $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($classLike);
            return $phpDocInfo->hasByType(ApiPhpDocTagNode::class);
        }

        return false;
    }
}
