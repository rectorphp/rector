<?php

declare (strict_types=1);
namespace Rector\CodingStyle\Rector\Encapsed;

use RectorPrefix202305\Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\BinaryOp\Concat;
use PhpParser\Node\Expr\ConstFetch;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Name;
use PhpParser\Node\Scalar\Encapsed;
use PhpParser\Node\Scalar\EncapsedStringPart;
use PhpParser\Node\Scalar\String_;
use PHPStan\Type\Type;
use Rector\Core\Rector\AbstractRector;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\CodingStyle\Rector\Encapsed\EncapsedStringsToSprintfRector\EncapsedStringsToSprintfRectorTest
 */
final class EncapsedStringsToSprintfRector extends AbstractRector
{
    /**
     * @var array<string, array<class-string<Type>>>
     */
    private const FORMAT_SPECIFIERS = ['%s' => ['PHPStan\\Type\\StringType'], '%d' => ['PHPStan\\Type\\Constant\\ConstantIntegerType', 'PHPStan\\Type\\IntegerRangeType', 'PHPStan\\Type\\IntegerType']];
    /**
     * @var string
     */
    private $sprintfFormat = '';
    /**
     * @var Expr[]
     */
    private $argumentVariables = [];
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Convert enscaped {$string} to more readable sprintf or concat, if no mask is used', [new CodeSample(<<<'CODE_SAMPLE'
echo "Unsupported format {$format}";
CODE_SAMPLE
, <<<'CODE_SAMPLE'
echo sprintf('Unsupported format %s', $format);
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [Encapsed::class];
    }
    /**
     * @param Encapsed $node
     */
    public function refactor(Node $node) : ?Node
    {
        if ($this->shouldSkip($node)) {
            return null;
        }
        $this->sprintfFormat = '';
        $this->argumentVariables = [];
        foreach ($node->parts as $part) {
            if ($part instanceof EncapsedStringPart) {
                $this->collectEncapsedStringPart($part);
            } else {
                $this->collectExpr($part);
            }
        }
        return $this->createSprintfFuncCallOrConcat($this->sprintfFormat, $this->argumentVariables);
    }
    private function shouldSkip(Encapsed $encapsed) : bool
    {
        $parentNode = $encapsed->getAttribute(AttributeKey::PARENT_NODE);
        if ($parentNode instanceof Arg) {
            $node = $parentNode->getAttribute(AttributeKey::PARENT_NODE);
            if ($node instanceof FuncCall && $this->isNames($node, ['_', 'dcgettext', 'dcngettext', 'dgettext', 'dngettext', 'gettext', 'ngettext'])) {
                return \true;
            }
        }
        return $encapsed->hasAttribute(AttributeKey::DOC_LABEL);
    }
    private function collectEncapsedStringPart(EncapsedStringPart $encapsedStringPart) : void
    {
        $stringValue = $encapsedStringPart->value;
        if ($stringValue === "\n") {
            $this->argumentVariables[] = new ConstFetch(new Name('PHP_EOL'));
            $this->sprintfFormat .= '%s';
            return;
        }
        $this->sprintfFormat .= Strings::replace($stringValue, '#%#', '%%');
    }
    private function collectExpr(Expr $expr) : void
    {
        $type = $this->nodeTypeResolver->getType($expr);
        $found = \false;
        foreach (self::FORMAT_SPECIFIERS as $key => $types) {
            if (\in_array(\get_class($type), $types, \true)) {
                $this->sprintfFormat .= $key;
                $found = \true;
                break;
            }
        }
        if (!$found) {
            $this->sprintfFormat .= '%s';
        }
        // remove: ${wrap} → $wrap
        if ($expr instanceof Variable) {
            $expr->setAttribute(AttributeKey::ORIGINAL_NODE, null);
        }
        $this->argumentVariables[] = $expr;
    }
    /**
     * @param Expr[] $argumentVariables
     * @return \PhpParser\Node\Expr\BinaryOp\Concat|\PhpParser\Node\Expr\FuncCall|\PhpParser\Node\Expr|null
     */
    private function createSprintfFuncCallOrConcat(string $mask, array $argumentVariables)
    {
        $bareMask = \str_repeat('%s', \count($argumentVariables));
        if ($mask === $bareMask) {
            if (\count($argumentVariables) === 1) {
                return $argumentVariables[0];
            }
            return $this->nodeFactory->createConcat($argumentVariables);
        }
        // checks for windows or linux line ending. \n is contained in both.
        if (\strpos($mask, "\n") !== \false) {
            return null;
        }
        $arguments = [new Arg(new String_($mask))];
        foreach ($argumentVariables as $argumentVariable) {
            $arguments[] = new Arg($argumentVariable);
        }
        return new FuncCall(new Name('sprintf'), $arguments);
    }
}
