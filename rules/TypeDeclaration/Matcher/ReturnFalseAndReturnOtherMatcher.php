<?php

declare (strict_types=1);
namespace Rector\TypeDeclaration\Matcher;

use PhpParser\Node\Expr;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Return_;
use Rector\Core\PhpParser\Node\BetterNodeFinder;
use Rector\Core\PhpParser\Node\Value\ValueResolver;
use Rector\TypeDeclaration\ValueObject\ReturnFalseAndReturnOther;
final class ReturnFalseAndReturnOtherMatcher
{
    /**
     * @readonly
     * @var \Rector\Core\PhpParser\Node\BetterNodeFinder
     */
    private $betterNodeFinder;
    /**
     * @readonly
     * @var \Rector\Core\PhpParser\Node\Value\ValueResolver
     */
    private $valueResolver;
    public function __construct(BetterNodeFinder $betterNodeFinder, ValueResolver $valueResolver)
    {
        $this->betterNodeFinder = $betterNodeFinder;
        $this->valueResolver = $valueResolver;
    }
    public function match(ClassMethod $classMethod) : ?ReturnFalseAndReturnOther
    {
        // there are 2 returns, one with false, other with something else except true
        /** @var Return_[] $returns */
        $returns = $this->betterNodeFinder->findInstancesOfInFunctionLikeScoped($classMethod, Return_::class);
        if (\count($returns) !== 2) {
            return null;
        }
        $firstReturn = $returns[0];
        $secondReturn = $returns[1];
        if (!$firstReturn->expr instanceof Expr) {
            return null;
        }
        if (!$secondReturn->expr instanceof Expr) {
            return null;
        }
        if ($this->valueResolver->isFalse($firstReturn->expr) && !$this->valueResolver->isTrueOrFalse($secondReturn->expr)) {
            return new ReturnFalseAndReturnOther($firstReturn, $secondReturn);
        }
        if (!$this->valueResolver->isFalse($secondReturn->expr)) {
            return null;
        }
        if ($this->valueResolver->isTrueOrFalse($firstReturn->expr)) {
            return null;
        }
        return new ReturnFalseAndReturnOther($secondReturn, $firstReturn);
    }
}
