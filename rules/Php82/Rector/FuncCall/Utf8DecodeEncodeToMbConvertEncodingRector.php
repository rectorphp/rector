<?php

declare (strict_types=1);
namespace Rector\Php82\Rector\FuncCall;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Name;
use PhpParser\Node\Scalar\String_;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\ValueObject\PhpVersionFeature;
use Rector\VersionBonding\Contract\MinPhpVersionInterface;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://wiki.php.net/rfc/remove_utf8_decode_and_utf8_encode
 *
 * @see https://3v4l.org/Q14UR
 * @see \Rector\Tests\Php82\Rector\FuncCall\Utf8DecodeEncodeToMbConvertEncodingRector\Utf8DecodeEncodeToMbConvertEncodingRectorTest
 */
final class Utf8DecodeEncodeToMbConvertEncodingRector extends AbstractRector implements MinPhpVersionInterface
{
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Change deprecated utf8_decode and utf8_encode to mb_convert_encoding', [new CodeSample(<<<'CODE_SAMPLE'
utf8_decode($value);
utf8_encode($value);
CODE_SAMPLE
, <<<'CODE_SAMPLE'
mb_convert_encoding($value, 'ISO-8859-1');
mb_convert_encoding($value, 'UTF-8', 'ISO-8859-1');
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
    public function refactor(Node $node) : ?Node
    {
        if ($node->isFirstClassCallable()) {
            return null;
        }
        if ($this->isName($node, 'utf8_decode')) {
            $node->name = new Name('mb_convert_encoding');
            $node->args[1] = new Arg(new String_('ISO-8859-1'));
            return $node;
        }
        if ($this->isName($node, 'utf8_encode')) {
            $node->name = new Name('mb_convert_encoding');
            $node->args[1] = new Arg(new String_('UTF-8'));
            $node->args[2] = new Arg(new String_('ISO-8859-1'));
            return $node;
        }
        return null;
    }
    public function provideMinPhpVersion() : int
    {
        return PhpVersionFeature::DEPRECATE_UTF8_DECODE_ENCODE_FUNCTION;
    }
}
