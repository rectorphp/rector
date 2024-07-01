<?php

declare (strict_types=1);
namespace Rector\CodingStyle\Rector\Stmt;

use RectorPrefix202407\Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\Identifier;
use PhpParser\Node\Stmt\Namespace_;
use PhpParser\Node\Stmt\Use_;
use Rector\PhpParser\Node\CustomNode\FileWithoutNamespace;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\CodingStyle\Rector\Stmt\RemoveUselessAliasInUseStatementRector\RemoveUselessAliasInUseStatementRectorTest
 */
final class RemoveUselessAliasInUseStatementRector extends AbstractRector
{
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Remove useless alias in use statement as same name with last use statement name', [new CodeSample(<<<'CODE_SAMPLE'
use App\Bar as Bar;
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use App\Bar;
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [FileWithoutNamespace::class, Namespace_::class];
    }
    /**
     * @param FileWithoutNamespace|Namespace_ $node
     * @return null|\Rector\PhpParser\Node\CustomNode\FileWithoutNamespace|\PhpParser\Node\Stmt\Namespace_
     */
    public function refactor(Node $node)
    {
        $hasChanged = \false;
        foreach ($node->stmts as $stmt) {
            if (!$stmt instanceof Use_) {
                continue;
            }
            if (\count($stmt->uses) !== 1) {
                continue;
            }
            if (!isset($stmt->uses[0])) {
                continue;
            }
            $aliasName = $stmt->uses[0]->alias instanceof Identifier ? $stmt->uses[0]->alias->toString() : null;
            if ($aliasName === null) {
                continue;
            }
            $useName = $stmt->uses[0]->name->toString();
            $lastName = Strings::after($useName, '\\', -1) ?? $useName;
            if ($lastName === $aliasName) {
                $stmt->uses[0]->alias = null;
                $hasChanged = \true;
            }
        }
        if ($hasChanged) {
            return $node;
        }
        return null;
    }
}
