<?php

declare (strict_types=1);
namespace Rector\TypeDeclaration\Rector\StmtsAwareInterface;

use PhpParser\Node;
use PhpParser\Node\Identifier;
use PhpParser\Node\Scalar\LNumber;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\Declare_;
use PhpParser\Node\Stmt\DeclareDeclare;
use PhpParser\Node\Stmt\InlineHTML;
use PhpParser\Node\Stmt\Nop;
use Rector\ChangesReporting\ValueObject\RectorWithLineChange;
use Rector\Contract\PhpParser\Node\StmtsAwareInterface;
use Rector\PhpParser\Node\CustomNode\FileWithoutNamespace;
use Rector\Rector\AbstractRector;
use Rector\TypeDeclaration\NodeAnalyzer\DeclareStrictTypeFinder;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\TypeDeclaration\Rector\StmtsAwareInterface\DeclareStrictTypesRector\DeclareStrictTypesRectorTest
 */
final class DeclareStrictTypesRector extends AbstractRector
{
    /**
     * @readonly
     * @var \Rector\TypeDeclaration\NodeAnalyzer\DeclareStrictTypeFinder
     */
    private $declareStrictTypeFinder;
    public function __construct(DeclareStrictTypeFinder $declareStrictTypeFinder)
    {
        $this->declareStrictTypeFinder = $declareStrictTypeFinder;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Add declare(strict_types=1) if missing', [new CodeSample(<<<'CODE_SAMPLE'
function someFunction()
{
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
declare(strict_types=1);

function someFunction()
{
}
CODE_SAMPLE
)]);
    }
    /**
     * @param Stmt[] $nodes
     * @return Stmt[]|null
     */
    public function beforeTraverse(array $nodes) : ?array
    {
        parent::beforeTraverse($nodes);
        $filePath = $this->file->getFilePath();
        if ($this->skipper->shouldSkipElementAndFilePath(self::class, $filePath)) {
            return null;
        }
        if ($nodes === []) {
            return null;
        }
        $rootStmt = \current($nodes);
        $stmt = $rootStmt;
        if ($rootStmt instanceof FileWithoutNamespace) {
            $currentStmt = \current($rootStmt->stmts);
            if (!$currentStmt instanceof Stmt) {
                return null;
            }
            if ($currentStmt instanceof InlineHTML) {
                return null;
            }
            $nodes = $rootStmt->stmts;
            $stmt = $currentStmt;
        }
        // when first stmt is Declare_, verify if there is strict_types definition already,
        // as multiple declare is allowed, with declare(strict_types=1) only allowed on very first stmt
        if ($this->declareStrictTypeFinder->hasDeclareStrictTypes($stmt)) {
            return null;
        }
        $declareDeclare = new DeclareDeclare(new Identifier('strict_types'), new LNumber(1));
        $strictTypesDeclare = new Declare_([$declareDeclare]);
        $rectorWithLineChange = new RectorWithLineChange(self::class, $stmt->getLine());
        $this->file->addRectorClassWithLine($rectorWithLineChange);
        if ($rootStmt instanceof FileWithoutNamespace) {
            /** @var Stmt[] $nodes */
            $rootStmt->stmts = \array_merge([$strictTypesDeclare, new Nop()], $nodes);
            return [$rootStmt];
        }
        return \array_merge([$strictTypesDeclare, new Nop()], $nodes);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [StmtsAwareInterface::class];
    }
    /**
     * @param StmtsAwareInterface $node
     */
    public function refactor(Node $node) : ?Node
    {
        // workaroudn, as Rector now only hooks to specific nodes, not arrays
        return null;
    }
}
