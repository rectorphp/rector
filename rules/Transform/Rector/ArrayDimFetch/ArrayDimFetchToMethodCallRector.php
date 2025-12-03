<?php

declare (strict_types=1);
namespace Rector\Transform\Rector\ArrayDimFetch;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\ArrayDimFetch;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\BinaryOp\BooleanAnd;
use PhpParser\Node\Expr\Isset_;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\Unset_;
use PHPStan\Type\ObjectType;
use Rector\Contract\Rector\ConfigurableRectorInterface;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\Rector\AbstractRector;
use Rector\Transform\Enum\MagicPropertyHandler;
use Rector\Transform\ValueObject\ArrayDimFetchToMethodCall;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
use RectorPrefix202512\Webmozart\Assert\Assert;
/**
 * @see \Rector\Tests\Transform\Rector\ArrayDimFetch\ArrayDimFetchToMethodCallRector\ArrayDimFetchToMethodCallRectorTest
 */
final class ArrayDimFetchToMethodCallRector extends AbstractRector implements ConfigurableRectorInterface
{
    /**
     * @var ArrayDimFetchToMethodCall[]
     */
    private array $arrayDimFetchToMethodCalls;
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Change array dim fetch to method call', [new ConfiguredCodeSample(<<<'CODE_SAMPLE'
$object['key'];
$object['key'] = 'value';
isset($object['key']);
unset($object['key']);
CODE_SAMPLE
, <<<'CODE_SAMPLE'
$object->get('key');
$object->set('key', 'value');
$object->has('key');
$object->unset('key');
CODE_SAMPLE
, [new ArrayDimFetchToMethodCall(new ObjectType('SomeClass'), 'get', 'set', 'has', 'unset')])]);
    }
    public function getNodeTypes(): array
    {
        return [Assign::class, Isset_::class, Unset_::class, ArrayDimFetch::class];
    }
    /**
     * @param ArrayDimFetch|Assign|Isset_|Unset_ $node
     * @return ($node is Unset_ ? Stmt[] : ($node is Isset_ ? Expr : MethodCall|null))
     */
    public function refactor(Node $node)
    {
        if ($node instanceof Unset_) {
            return $this->handleUnset($node);
        }
        if ($node instanceof Isset_) {
            return $this->handleIsset($node);
        }
        if ($node instanceof Assign) {
            if (!$node->var instanceof ArrayDimFetch) {
                return null;
            }
            return $this->createExplicitMethodCall($node->var, MagicPropertyHandler::SET, $node->expr);
        }
        // is part of assign, skip
        if ($node->getAttribute(AttributeKey::IS_BEING_ASSIGNED)) {
            return null;
        }
        if ($node->getAttribute(AttributeKey::IS_ASSIGN_OP_VAR)) {
            return null;
        }
        // should be skipped as handled above
        if ($node->getAttribute(AttributeKey::IS_UNSET_VAR)) {
            return null;
        }
        if ($node->getAttribute(AttributeKey::IS_ISSET_VAR)) {
            return null;
        }
        return $this->createExplicitMethodCall($node, MagicPropertyHandler::GET);
    }
    public function configure(array $configuration): void
    {
        Assert::allIsInstanceOf($configuration, ArrayDimFetchToMethodCall::class);
        $this->arrayDimFetchToMethodCalls = $configuration;
    }
    private function handleIsset(Isset_ $isset): ?\PhpParser\Node\Expr
    {
        $issets = [];
        $exprs = [];
        foreach ($isset->vars as $var) {
            if ($var instanceof ArrayDimFetch) {
                $methodCall = $this->createExplicitMethodCall($var, 'exists');
                if ($methodCall instanceof MethodCall) {
                    $exprs[] = $methodCall;
                    continue;
                }
            }
            $issets[] = $var;
        }
        if ($exprs === []) {
            // nothing to handle
            return null;
        }
        if ($issets !== []) {
            $isset->vars = $issets;
            array_unshift($exprs, $isset);
        }
        return array_reduce($exprs, fn(?Expr $carry, Expr $expr) => $carry instanceof Expr ? new BooleanAnd($carry, $expr) : $expr);
    }
    /**
     * @return Stmt[]|null
     */
    private function handleUnset(Unset_ $unset): ?array
    {
        $unsets = [];
        $stmts = [];
        foreach ($unset->vars as $var) {
            if ($var instanceof ArrayDimFetch) {
                $methodCall = $this->createExplicitMethodCall($var, 'unset');
                if ($methodCall instanceof MethodCall) {
                    $stmts[] = new Expression($methodCall);
                    continue;
                }
            }
            $unsets[] = $var;
        }
        // nothing to change
        if ($stmts === []) {
            return null;
        }
        if ($unsets !== []) {
            $unset->vars = $unsets;
            array_unshift($stmts, $unset);
        }
        return $stmts;
    }
    /**
     * @param MagicPropertyHandler::* $magicPropertyHandler
     */
    private function createExplicitMethodCall(ArrayDimFetch $arrayDimFetch, string $magicPropertyHandler, ?Expr $expr = null): ?MethodCall
    {
        if (!$arrayDimFetch->dim instanceof Node) {
            return null;
        }
        foreach ($this->arrayDimFetchToMethodCalls as $arrayDimFetchToMethodCall) {
            if (!$this->isObjectType($arrayDimFetch->var, $arrayDimFetchToMethodCall->getObjectType())) {
                continue;
            }
            switch ($magicPropertyHandler) {
                case MagicPropertyHandler::GET:
                    $method = $arrayDimFetchToMethodCall->getMethod();
                    break;
                case MagicPropertyHandler::SET:
                    $method = $arrayDimFetchToMethodCall->getSetMethod();
                    break;
                case MagicPropertyHandler::ISSET_:
                    $method = $arrayDimFetchToMethodCall->getExistsMethod();
                    break;
                case MagicPropertyHandler::UNSET:
                    $method = $arrayDimFetchToMethodCall->getUnsetMethod();
                    break;
            }
            if ($method === null) {
                continue;
            }
            $args = [new Arg($arrayDimFetch->dim)];
            if ($expr instanceof Expr) {
                $args[] = new Arg($expr);
            }
            return new MethodCall($arrayDimFetch->var, $method, $args);
        }
        return null;
    }
}
