<?php

declare (strict_types=1);
namespace Rector\Arguments\Rector\MethodCall;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Expr\Variable;
use Rector\Arguments\ValueObject\RemoveMethodCallParam;
use Rector\Contract\Rector\ConfigurableRectorInterface;
use Rector\NodeAnalyzer\ArgsAnalyzer;
use Rector\PhpParser\AstResolver;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
use RectorPrefix202601\Webmozart\Assert\Assert;
/**
 * @note used extensively https://github.com/search?q=RemoveMethodCallParamRector%3A%3Aclass+language%3APHP&type=code&l=PHP
 * @see \Rector\Tests\Arguments\Rector\MethodCall\RemoveMethodCallParamRector\RemoveMethodCallParamRectorTest
 */
final class RemoveMethodCallParamRector extends AbstractRector implements ConfigurableRectorInterface
{
    /**
     * @readonly
     */
    private AstResolver $astResolver;
    /**
     * @readonly
     */
    private ArgsAnalyzer $argsAnalyzer;
    /**
     * @var RemoveMethodCallParam[]
     */
    private array $removeMethodCallParams = [];
    public function __construct(AstResolver $astResolver, ArgsAnalyzer $argsAnalyzer)
    {
        $this->astResolver = $astResolver;
        $this->argsAnalyzer = $argsAnalyzer;
    }
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Remove parameter of method call', [new ConfiguredCodeSample(<<<'CODE_SAMPLE'
final class SomeClass
{
    public function run(Caller $caller)
    {
        $caller->process(1, 2);
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
final class SomeClass
{
    public function run(Caller $caller)
    {
        $caller->process(1);
    }
}
CODE_SAMPLE
, [new RemoveMethodCallParam('Caller', 'process', 1)])]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [MethodCall::class, StaticCall::class];
    }
    /**
     * @param MethodCall|StaticCall $node
     */
    public function refactor(Node $node): ?Node
    {
        $hasChanged = \false;
        if ($node->isFirstClassCallable()) {
            return null;
        }
        foreach ($this->removeMethodCallParams as $removeMethodCallParam) {
            if (!$this->isName($node->name, $removeMethodCallParam->getMethodName())) {
                continue;
            }
            if (!$this->isCallerObjectType($node, $removeMethodCallParam)) {
                continue;
            }
            $args = $node->getArgs();
            $position = $removeMethodCallParam->getParamPosition();
            $firstNamedArgPosition = $this->argsAnalyzer->resolveFirstNamedArgPosition($args);
            // if the call has named arguments and the argument that we want to remove is not
            // before any named argument, we need to check if it is in the list of named arguments
            // if it is, we use the position of the named argument as the position to remove
            // if it is not, we cannot remove it
            if ($firstNamedArgPosition !== null && $position >= $firstNamedArgPosition) {
                $call = $this->astResolver->resolveClassMethodOrFunctionFromCall($node);
                if ($call === null) {
                    return null;
                }
                $paramName = null;
                $variable = $call->params[$position]->var;
                if ($variable instanceof Variable) {
                    $paramName = $variable->name;
                }
                $newPosition = -1;
                if (is_string($paramName)) {
                    $newPosition = $this->argsAnalyzer->resolveArgPosition($args, $paramName, $newPosition);
                }
                if ($newPosition === -1) {
                    return null;
                }
                $position = $newPosition;
            }
            if (!isset($args[$position])) {
                continue;
            }
            unset($node->args[$position]);
            $hasChanged = \true;
        }
        if (!$hasChanged) {
            return null;
        }
        return $node;
    }
    /**
     * @param mixed[] $configuration
     */
    public function configure(array $configuration): void
    {
        Assert::allIsInstanceOf($configuration, RemoveMethodCallParam::class);
        $this->removeMethodCallParams = $configuration;
    }
    /**
     * @param \PhpParser\Node\Expr\StaticCall|\PhpParser\Node\Expr\MethodCall $call
     */
    private function isCallerObjectType($call, RemoveMethodCallParam $removeMethodCallParam): bool
    {
        if ($call instanceof MethodCall) {
            return $this->isObjectType($call->var, $removeMethodCallParam->getObjectType());
        }
        return $this->isObjectType($call->class, $removeMethodCallParam->getObjectType());
    }
}
