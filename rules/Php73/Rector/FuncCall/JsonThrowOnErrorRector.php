<?php

declare (strict_types=1);
namespace Rector\Php73\Rector\FuncCall;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\ConstFetch;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\Scalar\LNumber;
use Rector\Core\Contract\PhpParser\Node\StmtsAwareInterface;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\ValueObject\PhpVersionFeature;
use Rector\VersionBonding\Contract\MinPhpVersionInterface;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog http://wiki.php.net/rfc/json_throw_on_error
 * @changelog https://3v4l.org/5HMVE
 * @see \Rector\Tests\Php73\Rector\FuncCall\JsonThrowOnErrorRector\JsonThrowOnErrorRectorTest
 */
final class JsonThrowOnErrorRector extends AbstractRector implements MinPhpVersionInterface
{
    /**
     * @var bool
     */
    private $hasChanged = \false;
    public function getRuleDefinition() : RuleDefinition
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
    public function getNodeTypes() : array
    {
        return [StmtsAwareInterface::class];
    }
    /**
     * @param StmtsAwareInterface $node
     */
    public function refactor(Node $node) : ?Node
    {
        // if found, skip it :)
        $hasJsonErrorFuncCall = (bool) $this->betterNodeFinder->findFirst($node, function (Node $node) : bool {
            return $this->isNames($node, ['json_last_error', 'json_last_error_msg']);
        });
        if ($hasJsonErrorFuncCall) {
            return null;
        }
        $this->hasChanged = \false;
        $this->traverseNodesWithCallable($node, function (Node $currentNode) : ?FuncCall {
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
    public function provideMinPhpVersion() : int
    {
        return PhpVersionFeature::JSON_EXCEPTION;
    }
    private function shouldSkipFuncCall(FuncCall $funcCall) : bool
    {
        if ($funcCall->isFirstClassCallable()) {
            return \true;
        }
        if ($funcCall->args === null) {
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
    private function processJsonEncode(FuncCall $funcCall) : ?FuncCall
    {
        if (isset($funcCall->args[1])) {
            return null;
        }
        $this->hasChanged = \true;
        $funcCall->args[1] = new Arg($this->createConstFetch('JSON_THROW_ON_ERROR'));
        return $funcCall;
    }
    private function processJsonDecode(FuncCall $funcCall) : ?FuncCall
    {
        if (isset($funcCall->args[3])) {
            return null;
        }
        // set default to inter-args
        if (!isset($funcCall->args[1])) {
            $funcCall->args[1] = new Arg($this->nodeFactory->createNull());
        }
        if (!isset($funcCall->args[2])) {
            $funcCall->args[2] = new Arg(new LNumber(512));
        }
        $this->hasChanged = \true;
        $funcCall->args[3] = new Arg($this->createConstFetch('JSON_THROW_ON_ERROR'));
        return $funcCall;
    }
    private function createConstFetch(string $name) : ConstFetch
    {
        return new ConstFetch(new Name($name));
    }
    private function isFirstValueStringOrArray(FuncCall $funcCall) : bool
    {
        if (!isset($funcCall->getArgs()[0])) {
            return \false;
        }
        $firstArg = $funcCall->getArgs()[0];
        $value = $this->valueResolver->getValue($firstArg->value);
        if (\is_string($value)) {
            return \true;
        }
        return \is_array($value);
    }
}
