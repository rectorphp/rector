<?php

declare (strict_types=1);
namespace Rector\Symfony\Symfony44\Rector\MethodCall;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\ArrayItem;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\BinaryOp\BooleanOr;
use PhpParser\Node\Expr\MethodCall;
use PHPStan\Type\ObjectType;
use Rector\NodeAnalyzer\ArgsAnalyzer;
use Rector\Rector\AbstractRector;
use Rector\Symfony\Enum\SymfonyClass;
use Rector\Symfony\TypeAnalyzer\ControllerAnalyzer;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://github.com/symfony/symfony/blob/4.4/UPGRADE-4.4.md#security
 *
 * @see \Rector\Symfony\Tests\Symfony44\Rector\MethodCall\AuthorizationCheckerIsGrantedExtractorRector\AuthorizationCheckerIsGrantedExtractorRectorTest
 */
final class AuthorizationCheckerIsGrantedExtractorRector extends AbstractRector
{
    /**
     * @readonly
     */
    private ArgsAnalyzer $argsAnalyzer;
    /**
     * @readonly
     */
    private ControllerAnalyzer $controllerAnalyzer;
    public function __construct(ArgsAnalyzer $argsAnalyzer, ControllerAnalyzer $controllerAnalyzer)
    {
        $this->argsAnalyzer = $argsAnalyzer;
        $this->controllerAnalyzer = $controllerAnalyzer;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Change `$this->authorizationChecker->isGranted([$a, $b])` to `$this->authorizationChecker->isGranted($a) || $this->authorizationChecker->isGranted($b)`, also updates AbstractController usages', [new CodeSample(<<<'CODE_SAMPLE'
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

final class SomeController
{
    public function __construct(
        private AuthorizationCheckerInterface $authorizationChecker
    ) {
    }

    public function hasAccess(): bool
    {
        if ($this->authorizationChecker->isGranted(['ROLE_USER', 'ROLE_ADMIN'])) {
            return true;
        }

        return false;
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

final class SomeController
{
    public function __construct(
        private AuthorizationCheckerInterface $authorizationChecker
    ) {
    }

    public function hasAccess(): bool
    {
        if ($this->authorizationChecker->isGranted('ROLE_USER') || $this->authorizationChecker->isGranted('ROLE_ADMIN')) {
            return true;
        }

        return false;
    }
}
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [MethodCall::class];
    }
    /**
     * @param MethodCall $node
     * @return \PhpParser\Node\Expr\MethodCall|\PhpParser\Node\Expr\BinaryOp\BooleanOr|null
     */
    public function refactor(Node $node)
    {
        if ($this->controllerAnalyzer->isInsideController($node)) {
            return $this->processControllerMethods($node);
        }
        $objectType = $this->nodeTypeResolver->getType($node->var);
        if (!$objectType instanceof ObjectType) {
            return null;
        }
        $authorizationChecker = new ObjectType(SymfonyClass::AUTHORIZATION_CHECKER);
        if (!$authorizationChecker->isSuperTypeOf($objectType)->yes()) {
            return null;
        }
        if (!$this->isName($node->name, 'isGranted')) {
            return null;
        }
        return $this->handleIsGranted($node);
    }
    /**
     * @param Arg[] $args
     * @return \PhpParser\Node\Expr\MethodCall|\PhpParser\Node\Expr\BinaryOp\BooleanOr|null
     */
    private function processExtractIsGranted(MethodCall $methodCall, Array_ $array, array $args)
    {
        $exprs = [];
        foreach ($array->items as $item) {
            if ($item instanceof ArrayItem) {
                $exprs[] = $item->value;
            }
        }
        if ($exprs === []) {
            return null;
        }
        $args[0]->value = $exprs[0];
        $methodCall->args = $args;
        if (\count($exprs) === 1) {
            return $methodCall;
        }
        $rightMethodCall = clone $methodCall;
        $rightMethodCall->args[0] = new Arg($exprs[1]);
        $newMethodCallRight = new MethodCall($methodCall->var, $methodCall->name, $rightMethodCall->args, $methodCall->getAttributes());
        $booleanOr = new BooleanOr($methodCall, $newMethodCallRight);
        foreach ($exprs as $key => $expr) {
            if ($key <= 1) {
                continue;
            }
            $rightMethodCall = clone $methodCall;
            $rightMethodCall->args[0] = new Arg($expr);
            $newMethodCallRight = new MethodCall($methodCall->var, $methodCall->name, $rightMethodCall->args, $methodCall->getAttributes());
            $booleanOr = new BooleanOr($booleanOr, $newMethodCallRight);
        }
        return $booleanOr;
    }
    /**
     * @return \PhpParser\Node\Expr\MethodCall|\PhpParser\Node\Expr\BinaryOp\BooleanOr|null
     */
    private function processControllerMethods(MethodCall $methodCall)
    {
        if ($this->isName($methodCall->name, 'isGranted')) {
            return $this->handleIsGranted($methodCall);
        }
        return null;
    }
    /**
     * @return \PhpParser\Node\Expr\BinaryOp\BooleanOr|null|\PhpParser\Node\Expr\MethodCall
     */
    private function handleIsGranted(MethodCall $methodCall)
    {
        if ($methodCall->isFirstClassCallable()) {
            return null;
        }
        $args = $methodCall->getArgs();
        if ($this->argsAnalyzer->hasNamedArg($args)) {
            return null;
        }
        if (!isset($args[0])) {
            return null;
        }
        $value = $args[0]->value;
        if (!$value instanceof Array_) {
            return null;
        }
        return $this->processExtractIsGranted($methodCall, $value, $args);
    }
}
