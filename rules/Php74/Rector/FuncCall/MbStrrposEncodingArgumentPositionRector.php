<?php

declare (strict_types=1);
namespace Rector\Php74\Rector\FuncCall;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Scalar\Int_;
use Rector\Rector\AbstractRector;
use Rector\RuleDoc\CodeSample\CodeSample;
use Rector\RuleDoc\RuleDefinition;
use Rector\ValueObject\PhpVersion;
use Rector\VersionBonding\Contract\MinPhpVersionInterface;
/**
 * @see \Rector\Tests\Php74\Rector\FuncCall\MbStrrposEncodingArgumentPositionRector\MbStrrposEncodingArgumentPositionRectorTest
 */
final class MbStrrposEncodingArgumentPositionRector extends AbstractRector implements MinPhpVersionInterface
{
    public function provideMinPhpVersion(): int
    {
        return PhpVersion::PHP_74;
    }
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Change mb_strrpos() encoding argument position', [new CodeSample('mb_strrpos($text, "abc", "UTF-8");', 'mb_strrpos($text, "abc", 0, "UTF-8");')]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [FuncCall::class];
    }
    /**
     * @param FuncCall $node
     */
    public function refactor(Node $node): ?Node
    {
        if (!$this->isName($node, 'mb_strrpos')) {
            return null;
        }
        // encoding already passed, positionally or by name
        if ($node->getArg('encoding', 3) instanceof Arg) {
            return null;
        }
        $offsetArg = $node->getArg('offset', 2);
        if (!$offsetArg instanceof Arg) {
            return null;
        }
        // only string can be an encoding; mixed/unknown can be the int $offset
        $offsetArgType = $this->nodeTypeResolver->getNativeType($offsetArg->value);
        if (!$offsetArgType->isString()->yes()) {
            return null;
        }
        $node->args[3] = $offsetArg;
        $node->args[2] = new Arg(new Int_(0));
        return $node;
    }
}
