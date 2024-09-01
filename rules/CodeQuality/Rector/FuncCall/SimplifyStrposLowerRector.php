<?php

declare (strict_types=1);
namespace Rector\CodeQuality\Rector\FuncCall;

use RectorPrefix202409\Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Name;
use PhpParser\Node\Scalar\String_;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\CodeQuality\Rector\FuncCall\SimplifyStrposLowerRector\SimplifyStrposLowerRectorTest
 */
final class SimplifyStrposLowerRector extends AbstractRector
{
    /**
     * @var string
     * @see https://regex101.com/r/Jokjt8/1
     */
    private const UPPERCASE_REGEX = '#[A-Z]#';
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Simplify strpos(strtolower(), "...") calls', [new CodeSample('strpos(strtolower($var), "...")', 'stripos($var, "...")')]);
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
        if (!$this->isName($node, 'strpos')) {
            return null;
        }
        if ($node->isFirstClassCallable()) {
            return null;
        }
        $args = $node->getArgs();
        if (!isset($args[0], $args[1])) {
            return null;
        }
        $firstArg = $args[0];
        if (!$firstArg->value instanceof FuncCall) {
            return null;
        }
        /** @var FuncCall $innerFuncCall */
        $innerFuncCall = $firstArg->value;
        if (!$this->isName($innerFuncCall, 'strtolower')) {
            return null;
        }
        $secondArg = $args[1];
        if (!$secondArg->value instanceof String_) {
            return null;
        }
        if (Strings::match($secondArg->value->value, self::UPPERCASE_REGEX) !== null) {
            return null;
        }
        // pop 1 level up
        $node->args[0] = $innerFuncCall->getArgs()[0];
        $node->name = new Name('stripos');
        return $node;
    }
}
