<?php

declare (strict_types=1);
namespace Rector\Php74\Rector\FuncCall;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Scalar\Int_;
use Rector\Rector\AbstractRector;
use Rector\ValueObject\PhpVersionFeature;
use Rector\VersionBonding\Contract\MinPhpVersionInterface;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\Php74\Rector\FuncCall\MbStrrposEncodingArgumentPositionRector\MbStrrposEncodingArgumentPositionRectorTest
 */
final class MbStrrposEncodingArgumentPositionRector extends AbstractRector implements MinPhpVersionInterface
{
    public function provideMinPhpVersion() : int
    {
        return PhpVersionFeature::CHANGE_MB_STRPOS_ARG_POSITION;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Change mb_strrpos() encoding argument position', [new CodeSample('mb_strrpos($text, "abc", "UTF-8");', 'mb_strrpos($text, "abc", 0, "UTF-8");')]);
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
    public function refactor(Node $node) : ?Node
    {
        if (!$this->isName($node, 'mb_strrpos')) {
            return null;
        }
        if (!isset($node->args[2])) {
            return null;
        }
        if (isset($node->args[3])) {
            return null;
        }
        if (!$node->args[2] instanceof Arg) {
            return null;
        }
        $secondArgType = $this->getType($node->args[2]->value);
        if ($secondArgType->isInteger()->yes()) {
            return null;
        }
        $node->args[3] = $node->args[2];
        $node->args[2] = new Arg(new Int_(0));
        return $node;
    }
}
