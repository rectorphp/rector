<?php

declare (strict_types=1);
namespace Rector\TypeDeclaration\Rector\StmtsAwareInterface;

use PhpParser\Node;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\Nop;
use PhpParser\NodeVisitor;
use Rector\ChangesReporting\ValueObject\RectorWithLineChange;
use Rector\Contract\PhpParser\Node\StmtsAwareInterface;
use Rector\Contract\Rector\HTMLAverseRectorInterface;
use Rector\PhpParser\Node\CustomNode\FileWithoutNamespace;
use Rector\Rector\AbstractRector;
use Rector\TypeDeclaration\NodeAnalyzer\DeclareStrictTypeFinder;
use Rector\ValueObject\Application\File;
use Rector\ValueObject\PhpVersion;
use Rector\VersionBonding\Contract\MinPhpVersionInterface;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\TypeDeclaration\Rector\StmtsAwareInterface\DeclareStrictTypesRector\DeclareStrictTypesRectorTest
 */
final class DeclareStrictTypesRector extends AbstractRector implements HTMLAverseRectorInterface, MinPhpVersionInterface
{
    /**
     * @readonly
     */
    private DeclareStrictTypeFinder $declareStrictTypeFinder;
    public function __construct(DeclareStrictTypeFinder $declareStrictTypeFinder)
    {
        $this->declareStrictTypeFinder = $declareStrictTypeFinder;
    }
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Add `declare(strict_types=1)` if missing in a namespaced file', [new CodeSample(<<<'CODE_SAMPLE'
namespace App;

class SomeClass
{
    function someFunction(int $number)
    {
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
declare(strict_types=1);

namespace App;

class SomeClass
{
    function someFunction(int $number)
    {
    }
}
CODE_SAMPLE
)]);
    }
    /**
     * @param Stmt[] $nodes
     * @return Stmt[]|null
     */
    public function beforeTraverse(array $nodes): ?array
    {
        parent::beforeTraverse($nodes);
        if ($this->shouldSkipNodes($nodes, $this->file)) {
            return null;
        }
        /** @var Node $rootStmt */
        $rootStmt = current($nodes);
        if ($rootStmt instanceof FileWithoutNamespace) {
            return null;
        }
        // when first stmt is Declare_, verify if there is strict_types definition already,
        // as multiple declare is allowed, with declare(strict_types=1) only allowed on very first stmt
        if ($this->declareStrictTypeFinder->hasDeclareStrictTypes($rootStmt)) {
            return null;
        }
        $rectorWithLineChange = new RectorWithLineChange(self::class, $rootStmt->getStartLine());
        $this->file->addRectorClassWithLine($rectorWithLineChange);
        return array_merge([$this->nodeFactory->createDeclaresStrictType(), new Nop()], $nodes);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [StmtsAwareInterface::class];
    }
    /**
     * @param StmtsAwareInterface $node
     */
    public function refactor(Node $node): int
    {
        // workaround, as Rector now only hooks to specific nodes, not arrays
        // avoid traversing, as we already handled in beforeTraverse()
        return NodeVisitor::DONT_TRAVERSE_CURRENT_AND_CHILDREN;
    }
    public function provideMinPhpVersion(): int
    {
        return PhpVersion::PHP_70;
    }
    /**
     * @param Stmt[] $nodes
     */
    private function shouldSkipNodes(array $nodes, File $file): bool
    {
        if ($this->skipper->shouldSkipElementAndFilePath(self::class, $file->getFilePath())) {
            return \true;
        }
        if (strncmp($file->getFileContent(), '#!', strlen('#!')) === 0) {
            return \true;
        }
        return $nodes === [];
    }
}
