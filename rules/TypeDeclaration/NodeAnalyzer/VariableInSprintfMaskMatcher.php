<?php

declare (strict_types=1);
namespace Rector\TypeDeclaration\NodeAnalyzer;

use RectorPrefix202510\Nette\Utils\Strings;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Function_;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\NodeTypeResolver\NodeTypeResolver;
use Rector\PhpParser\Node\BetterNodeFinder;
use Rector\PhpParser\Node\Value\ValueResolver;
final class VariableInSprintfMaskMatcher
{
    /**
     * @readonly
     */
    private BetterNodeFinder $betterNodeFinder;
    /**
     * @readonly
     */
    private NodeNameResolver $nodeNameResolver;
    /**
     * @readonly
     */
    private NodeTypeResolver $nodeTypeResolver;
    /**
     * @readonly
     */
    private ValueResolver $valueResolver;
    public function __construct(BetterNodeFinder $betterNodeFinder, NodeNameResolver $nodeNameResolver, NodeTypeResolver $nodeTypeResolver, ValueResolver $valueResolver)
    {
        $this->betterNodeFinder = $betterNodeFinder;
        $this->nodeNameResolver = $nodeNameResolver;
        $this->nodeTypeResolver = $nodeTypeResolver;
        $this->valueResolver = $valueResolver;
    }
    /**
     * @param \PhpParser\Node\Stmt\ClassMethod|\PhpParser\Node\Stmt\Function_ $functionLike
     */
    public function matchMask($functionLike, string $variableName, string $mask): bool
    {
        $funcCalls = $this->betterNodeFinder->findInstancesOfScoped((array) $functionLike->stmts, FuncCall::class);
        foreach ($funcCalls as $funcCall) {
            if (!$this->nodeNameResolver->isName($funcCall->name, 'sprintf')) {
                continue;
            }
            if ($funcCall->isFirstClassCallable()) {
                continue;
            }
            $args = $funcCall->getArgs();
            if (count($args) < 2) {
                continue;
            }
            /** @var Arg $messageArg */
            $messageArg = array_shift($args);
            $type = $this->nodeTypeResolver->getType($messageArg->value);
            $messageValue = $this->valueResolver->getValue($messageArg->value);
            if (!is_string($messageValue)) {
                continue;
            }
            // match all %s, %d types by position
            $masks = Strings::match($messageValue, '#%[sd]#');
            foreach ($args as $position => $arg) {
                if (!$arg->value instanceof Variable) {
                    continue;
                }
                if (!$this->nodeNameResolver->isName($arg->value, $variableName)) {
                    continue;
                }
                if (!isset($masks[$position])) {
                    continue;
                }
                $knownMaskOnPosition = $masks[$position];
                if ($knownMaskOnPosition !== $mask) {
                    continue;
                }
                return \true;
            }
        }
        return \false;
    }
}
