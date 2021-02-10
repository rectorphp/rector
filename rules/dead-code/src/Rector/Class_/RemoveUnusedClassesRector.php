<?php

declare(strict_types=1);

namespace Rector\DeadCode\Rector\Class_;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use Rector\BetterPhpDocParser\ValueObject\PhpDocNode\ApiPhpDocTagNode;
use Rector\Caching\Contract\Rector\ZeroCacheRectorInterface;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\Core\Rector\AbstractRector;
use Rector\DeadCode\UnusedNodeResolver\UnusedClassResolver;
use Rector\Doctrine\PhpDocParser\DoctrineDocBlockResolver;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\Testing\PHPUnit\StaticPHPUnitEnvironment;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
use Symplify\SmartFileSystem\SmartFileInfo;

/**
 * @see \Rector\DeadCode\Tests\Rector\Class_\RemoveUnusedClassesRector\RemoveUnusedClassesRectorTest
 */
final class RemoveUnusedClassesRector extends AbstractRector implements ZeroCacheRectorInterface
{
    /**
     * @var UnusedClassResolver
     */
    private $unusedClassResolver;

    /**
     * @var DoctrineDocBlockResolver
     */
    private $doctrineDocBlockResolver;

    public function __construct(
        UnusedClassResolver $unusedClassResolver,
        DoctrineDocBlockResolver $doctrineDocBlockResolver
    ) {
        $this->unusedClassResolver = $unusedClassResolver;
        $this->doctrineDocBlockResolver = $doctrineDocBlockResolver;
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Remove unused classes without interface', [
            new CodeSample(
                <<<'CODE_SAMPLE'
interface SomeInterface
{
}

class SomeClass implements SomeInterface
{
    public function run($items)
    {
        return null;
    }
}

class NowhereUsedClass
{
}
CODE_SAMPLE
,
                <<<'CODE_SAMPLE'
interface SomeInterface
{
}

class SomeClass implements SomeInterface
{
    public function run($items)
    {
        return null;
    }
}
CODE_SAMPLE
            ),
        ]);
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [Class_::class];
    }

    /**
     * @param Class_ $node
     */
    public function refactor(Node $node): ?Node
    {
        if ($this->shouldSkip($node)) {
            return null;
        }

        if ($this->unusedClassResolver->isClassUsed($node)) {
            return null;
        }

        if (StaticPHPUnitEnvironment::isPHPUnitRun()) {
            $this->removeNode($node);
        } else {
            $smartFileInfo = $node->getAttribute(AttributeKey::FILE_INFO);
            if (! $smartFileInfo instanceof SmartFileInfo) {
                throw new ShouldNotHappenException();
            }

            $this->removedAndAddedFilesCollector->removeFile($smartFileInfo);
        }

        return null;
    }

    private function shouldSkip(Class_ $class): bool
    {
        if (! $this->unusedClassResolver->isClassWithoutInterfaceAndNotController($class)) {
            return true;
        }

        if ($this->doctrineDocBlockResolver->isDoctrineEntityClass($class)) {
            return true;
        }

        // most of factories can be only registered in config and create services there
        // skip them for now; but in the future, detect types they create in public methods and only keep them, if they're used
        if ($this->isName($class, '*Factory')) {
            return true;
        }

        if ($this->hasMethodWithApiAnnotation($class)) {
            return true;
        }

        $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($class);
        if ($phpDocInfo->hasByType(ApiPhpDocTagNode::class)) {
            return true;
        }

        if ($class->isAbstract()) {
            return true;
        }

        return $this->nodeRepository->hasClassChildren($class);
    }

    private function hasMethodWithApiAnnotation(Class_ $class): bool
    {
        foreach ($class->getMethods() as $classMethod) {
            $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($classMethod);
            if (! $phpDocInfo->hasByType(ApiPhpDocTagNode::class)) {
                continue;
            }

            return true;
        }

        return false;
    }
}
