<?php

declare (strict_types=1);
namespace Rector\Php73\Rector\FuncCall;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\BinaryOp\BitwiseOr;
use PhpParser\Node\Expr\ConstFetch;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\Scalar\Int_;
use Rector\PhpParser\Enum\NodeGroup;
use Rector\PhpParser\Node\BetterNodeFinder;
use Rector\PhpParser\Node\Value\ValueResolver;
use Rector\Rector\AbstractRector;
use Rector\ValueObject\PhpVersionFeature;
use Rector\VersionBonding\Contract\MinPhpVersionInterface;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\Php73\Rector\FuncCall\JsonThrowOnErrorRector\JsonThrowOnErrorRectorTest
 */
final class JsonThrowOnErrorRector extends AbstractRector implements MinPhpVersionInterface
{
    /**
     * @readonly
     */
    private ValueResolver $valueResolver;
    /**
     * @readonly
     */
    private BetterNodeFinder $betterNodeFinder;
    /**
     * @var mixed[]
     */
    private const FLAGS = ['JSON_THROW_ON_ERROR'];
    private bool $hasChanged = \false;
    public function __construct(ValueResolver $valueResolver, BetterNodeFinder $betterNodeFinder)
    {
        $this->valueResolver = $valueResolver;
        $this->betterNodeFinder = $betterNodeFinder;
    }
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Adds JSON_THROW_ON_ERROR to json_encode() and json_decode() to throw JsonException on error', [new CodeSample(<<<'CODE_SAMPLE'
json_encode($content);
json_decode($json);
CODE_SAMPLE
, <<<'CODE_SAMPLE'
json_encode($content, JSON_THROW_ON_ERROR);
json_decode($json, null, 512, JSON_THROW_ON_ERROR);
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return NodeGroup::STMTS_AWARE;
    }
    public function refactor(Node $node): ?Node
    {
        // if found, skip it :)
        $hasJsonErrorFuncCall = (bool) $this->betterNodeFinder->findFirst($node, fn(Node $node): bool => $this->isNames($node, ['json_last_error', 'json_last_error_msg']));
        if ($hasJsonErrorFuncCall) {
            return null;
        }
        $this->hasChanged = \false;
        $this->traverseNodesWithCallable($node, function (Node $currentNode): ?FuncCall {
            if (!$currentNode instanceof FuncCall) {
                return null;
            }
            if ($this->shouldSkipFuncCall($currentNode)) {
                return null;
            }
            if ($this->isName($currentNode, 'json_encode')) {
                return $this->processJsonEncode($currentNode);
            }
            if ($this->isName($currentNode, 'json_decode')) {
                return $this->processJsonDecode($currentNode);
            }
            return null;
        });
        if ($this->hasChanged) {
            return $node;
        }
        return null;
    }
    public function provideMinPhpVersion(): int
    {
        return PhpVersionFeature::JSON_EXCEPTION;
    }
    private function shouldSkipFuncCall(FuncCall $funcCall): bool
    {
        if ($funcCall->isFirstClassCallable()) {
            return \true;
        }
        if ($funcCall->args === []) {
            return \true;
        }
        foreach ($funcCall->args as $arg) {
            if (!$arg instanceof Arg) {
                continue;
            }
            if ($arg->name instanceof Identifier) {
                return \true;
            }
        }
        return $this->isFirstValueStringOrArray($funcCall);
    }
    private function processJsonEncode(FuncCall $funcCall): FuncCall
    {
        $flags = [];
        if (isset($funcCall->args[1])) {
            /** @var Arg $arg */
            $arg = $funcCall->args[1];
            $flags = $this->getFlags($arg);
        }
        $newArg = $this->getArgWithFlags($flags);
        if ($newArg instanceof Arg) {
            $this->hasChanged = \true;
            $funcCall->args[1] = $newArg;
        }
        return $funcCall;
    }
    private function processJsonDecode(FuncCall $funcCall): FuncCall
    {
        $flags = [];
        if (isset($funcCall->args[3])) {
            /** @var Arg $arg */
            $arg = $funcCall->args[3];
            $flags = $this->getFlags($arg);
        }
        // set default to inter-args
        if (!isset($funcCall->args[1])) {
            $funcCall->args[1] = new Arg($this->nodeFactory->createNull());
        }
        if (!isset($funcCall->args[2])) {
            $funcCall->args[2] = new Arg(new Int_(512));
        }
        $newArg = $this->getArgWithFlags($flags);
        if ($newArg instanceof Arg) {
            $this->hasChanged = \true;
            $funcCall->args[3] = $newArg;
        }
        return $funcCall;
    }
    private function createConstFetch(string $name): ConstFetch
    {
        return new ConstFetch(new Name($name));
    }
    private function isFirstValueStringOrArray(FuncCall $funcCall): bool
    {
        if (!isset($funcCall->getArgs()[0])) {
            return \false;
        }
        $firstArg = $funcCall->getArgs()[0];
        $value = $this->valueResolver->getValue($firstArg->value);
        if (is_string($value)) {
            return \true;
        }
        return is_array($value);
    }
    /**
     * @param string[] $flags
     * @return string[]
     * @param \PhpParser\Node\Expr|\PhpParser\Node\Arg $arg
     */
    private function getFlags($arg, array $flags = []): array
    {
        // Unwrap Arg
        if ($arg instanceof Arg) {
            $arg = $arg->value;
        }
        // Single flag: SOME_CONST
        if ($arg instanceof ConstFetch) {
            $flags[] = $arg->name->getFirst();
            return $flags;
        }
        // Multiple flags: FLAG_A | FLAG_B | FLAG_C
        if ($arg instanceof BitwiseOr) {
            $flags = $this->getFlags($arg->left, $flags);
            $flags = $this->getFlags($arg->right, $flags);
        }
        return array_values(array_unique($flags));
        // array_unique in case the same flag is written multiple times
    }
    /**
     * @param string[] $flags
     */
    private function getArgWithFlags(array $flags): ?Arg
    {
        $originalCount = count($flags);
        $flags = array_values(array_unique(array_merge($flags, self::FLAGS)));
        if ($originalCount === count($flags)) {
            return null;
        }
        // Single flag
        if (count($flags) === 1) {
            return new Arg($this->createConstFetch($flags[0]));
        }
        // Build FLAG_A | FLAG_B | FLAG_C
        $expr = $this->createConstFetch(array_shift($flags));
        foreach ($flags as $flag) {
            $expr = new BitwiseOr($expr, $this->createConstFetch($flag));
        }
        return new Arg($expr);
    }
}
