<?php

declare(strict_types=1);

namespace Rector\DeadCode\Rector\ClassConst;

use PhpParser\Node;
use PhpParser\Node\Stmt\ClassConst;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use Rector\Caching\Contract\Rector\ZeroCacheRectorInterface;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;
use Rector\NodeCollector\NodeFinder\ClassConstParsedNodesFinder;
use Rector\NodeTypeResolver\Node\AttributeKey;

/**
 * @see \Rector\DeadCode\Tests\Rector\ClassConst\RemoveUnusedClassConstantRector\RemoveUnusedClassConstantRectorTest
 */
final class RemoveUnusedClassConstantRector extends AbstractRector implements ZeroCacheRectorInterface
{
    /**
     * @var ClassConstParsedNodesFinder
     */
    private $classConstParsedNodesFinder;

    public function __construct(ClassConstParsedNodesFinder $classConstParsedNodesFinder)
    {
        $this->classConstParsedNodesFinder = $classConstParsedNodesFinder;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Remove unused class constants', [
            new CodeSample(
                <<<'PHP'
class SomeClass
{
    private const SOME_CONST = 'dead';

    public function run()
    {
    }
}
PHP
,
                <<<'PHP'
class SomeClass
{
    public function run()
    {
    }
}
PHP
            ),
        ]);
    }

    /**
     * @return string[]
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

        /** @var string|null $class */
        $class = $node->getAttribute(AttributeKey::CLASS_NAME);
        if ($class === null) {
            return null;
        }

        // 0. constants declared in interfaces have to be public
        if ($this->classLikeParsedNodesFinder->findInterface($class) !== null) {
            $this->makePublic($node);
            return $node;
        }

        /** @var string $constant */
        $constant = $this->getName($node);

        $directUseClasses = $this->classConstParsedNodesFinder->findDirectClassConstantFetches($class, $constant);
        if ($directUseClasses !== []) {
            return null;
        }

        $indirectUseClasses = $this->classConstParsedNodesFinder->findIndirectClassConstantFetches($class, $constant);
        if ($indirectUseClasses !== []) {
            return null;
        }

        $this->removeNode($node);

        return null;
    }

    /**
     * @param ClassConst $node
     */
    private function shouldSkip(Node $node): bool
    {
        if ($this->isOpenSourceProjectType()) {
            return true;
        }

        if (count($node->consts) !== 1) {
            return true;
        }

        /** @var PhpDocInfo|null $phpDocInfo */
        $phpDocInfo = $node->getAttribute(AttributeKey::PHP_DOC_INFO);
        if ($phpDocInfo === null) {
            return false;
        }

        return $phpDocInfo->hasByName('api');
    }
}
