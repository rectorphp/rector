<?php

declare (strict_types=1);
namespace Rector\TypeDeclaration\NodeAnalyzer;

use RectorPrefix202510\Nette\Utils\Strings;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Function_;
use PHPStan\Type\MixedType;
use PHPStan\Type\UnionType;
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
        $funcCalls = array_values(array_filter($funcCalls, function (FuncCall $funcCall): bool {
            return $this->nodeNameResolver->isName($funcCall->name, 'sprintf');
        }));
        if (count($funcCalls) !== 1) {
            return \false;
        }
        $funcCall = $funcCalls[0];
        if ($funcCall->isFirstClassCallable()) {
            return \false;
        }
        $args = $funcCall->getArgs();
        if (count($args) < 2) {
            return \false;
        }
        /** @var Arg $messageArg */
        $messageArg = array_shift($args);
        $messageValue = $this->valueResolver->getValue($messageArg->value);
        if (!is_string($messageValue)) {
            return \false;
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
            $type = $this->nodeTypeResolver->getNativeType($arg->value);
            if ($type instanceof MixedType && $type->getSubtractedType() instanceof UnionType) {
                continue;
            }
            return \true;
        }
        return \false;
    }
}
