<?php

declare (strict_types=1);
namespace Rector\TypeDeclaration\Rector\StmtsAwareInterface;

use PhpParser\Node;
use PhpParser\Node\Stmt\Nop;
use Rector\Contract\Rector\HTMLAverseRectorInterface;
use Rector\PhpParser\Node\FileNode;
use Rector\Rector\AbstractRector;
use Rector\TypeDeclaration\NodeAnalyzer\DeclareStrictTypeFinder;
use Rector\TypeDeclaration\NodeAnalyzer\StrictTypeSafetyChecker;
use Rector\ValueObject\PhpVersion;
use Rector\VersionBonding\Contract\MinPhpVersionInterface;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\TypeDeclaration\Rector\StmtsAwareInterface\SafeDeclareStrictTypesRector\SafeDeclareStrictTypesRectorTest
 */
final class SafeDeclareStrictTypesRector extends AbstractRector implements HTMLAverseRectorInterface, MinPhpVersionInterface
{
    /**
     * @readonly
     */
    private DeclareStrictTypeFinder $declareStrictTypeFinder;
    /**
     * @readonly
     */
    private StrictTypeSafetyChecker $strictTypeSafetyChecker;
    public function __construct(DeclareStrictTypeFinder $declareStrictTypeFinder, StrictTypeSafetyChecker $strictTypeSafetyChecker)
    {
        $this->declareStrictTypeFinder = $declareStrictTypeFinder;
        $this->strictTypeSafetyChecker = $strictTypeSafetyChecker;
    }
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Add `declare(strict_types=1)` if missing and only if the file is type-safe (no scalar type coercions).', [new CodeSample(<<<'CODE_SAMPLE'
function acceptsInt(int $value): void
{
}

acceptsInt(5);
CODE_SAMPLE
, <<<'CODE_SAMPLE'
declare(strict_types=1);

function acceptsInt(int $value): void
{
}

acceptsInt(5);
CODE_SAMPLE
)]);
    }
    /**
     * @param FileNode $node
     */
    public function refactor(Node $node): ?FileNode
    {
        if ($this->declareStrictTypeFinder->hasDeclareStrictTypes($node)) {
            return null;
        }
        if (!$this->strictTypeSafetyChecker->isFileStrictTypeSafe($node)) {
            return null;
        }
        $declaresStrictType = $this->nodeFactory->createDeclaresStrictType();
        $node->stmts = array_merge([$declaresStrictType, new Nop()], $node->stmts);
        return $node;
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [FileNode::class];
    }
    public function provideMinPhpVersion(): int
    {
        return PhpVersion::PHP_70;
    }
}
