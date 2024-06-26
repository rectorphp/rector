<?php

declare (strict_types=1);
namespace Rector\Php74\Rector\FuncCall;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Name;
use PhpParser\Node\Scalar\String_;
use Rector\Rector\AbstractRector;
use Rector\ValueObject\PhpVersionFeature;
use Rector\VersionBonding\Contract\MinPhpVersionInterface;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see https://3v4l.org/Kcs98
 * @see \Rector\Tests\Php74\Rector\FuncCall\RestoreIncludePathToIniRestoreRector\RestoreIncludePathToIniRestoreRectorTest
 */
final class RestoreIncludePathToIniRestoreRector extends AbstractRector implements MinPhpVersionInterface
{
    public function provideMinPhpVersion() : int
    {
        return PhpVersionFeature::DEPRECATE_RESTORE_INCLUDE_PATH;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Change restore_include_path() to ini_restore("include_path")', [new CodeSample(<<<'CODE_SAMPLE'
restore_include_path();
CODE_SAMPLE
, <<<'CODE_SAMPLE'
ini_restore('include_path');
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [FuncCall::class];
    }
    /**
     * @param FuncCall $node
     */
    public function refactor(Node $node) : ?FuncCall
    {
        if (!$this->isName($node, 'restore_include_path')) {
            return null;
        }
        if ($node->isFirstClassCallable()) {
            return null;
        }
        $node->name = new Name('ini_restore');
        $node->args[0] = new Arg(new String_('include_path'));
        return $node;
    }
}
