<?php

declare (strict_types=1);
namespace Rector\TypeDeclaration\Rector\StmtsAwareInterface;

use PhpParser\Node;
use PhpParser\Node\Identifier;
use PhpParser\Node\Scalar\LNumber;
use PhpParser\Node\Stmt\Declare_;
use PhpParser\Node\Stmt\DeclareDeclare;
use PhpParser\Node\Stmt\Nop;
use Rector\ChangesReporting\ValueObject\RectorWithLineChange;
use Rector\Core\Contract\PhpParser\Node\StmtsAwareInterface;
use Rector\Core\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\TypeDeclaration\Rector\StmtsAwareInterface\DeclareStrictTypesRector\DeclareStrictTypesRectorTest
 */
final class DeclareStrictTypesRector extends AbstractRector
{
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
     * @param Node[] $nodes
     * @return Node[]|null
     */
    public function beforeTraverse(array $nodes) : ?array
    {
        parent::beforeTraverse($nodes);
        $newStmts = $this->file->getNewStmts();
        if ($newStmts === []) {
            return null;
        }
        $stmt = \current($newStmts);
        // when first stmt is Declare_, verify if there is strict_types definition already,
        // as multiple declare is allowed, with declare(strict_types=1) only allowed on very first stmt
        if ($stmt instanceof Declare_) {
            foreach ($stmt->declares as $declare) {
                if ($declare->key->toString() === 'strict_types') {
                    return null;
                }
            }
        }
        $declareDeclare = new DeclareDeclare(new Identifier('strict_types'), new LNumber(1));
        $strictTypesDeclare = new Declare_([$declareDeclare]);
        $rectorWithLineChange = new RectorWithLineChange(self::class, $stmt->getLine());
        $this->file->addRectorClassWithLine($rectorWithLineChange);
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
