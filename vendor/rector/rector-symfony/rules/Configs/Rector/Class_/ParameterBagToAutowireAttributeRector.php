<?php

declare (strict_types=1);
namespace Rector\Symfony\Configs\Rector\Class_;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Param;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PHPStan\Type\ObjectType;
use Rector\Rector\AbstractRector;
use Rector\Symfony\Configs\NodeFactory\AutowiredParamFactory;
use Rector\ValueObject\MethodName;
use Rector\ValueObject\PhpVersionFeature;
use Rector\VersionBonding\Contract\MinPhpVersionInterface;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * The param/env is only available since Symfony 6.3
 * @see https://symfony.com/blog/new-in-symfony-6-3-dependency-injection-improvements#new-options-for-autowire-attribute
 *
 * @see \Rector\Symfony\Tests\Configs\Rector\Class_\ParameterBagToAutowireAttributeRector\ParameterBagToAutowireAttributeRectorTest
 */
final class ParameterBagToAutowireAttributeRector extends AbstractRector implements MinPhpVersionInterface
{
    /**
     * @readonly
     */
    private AutowiredParamFactory $autowiredParamFactory;
    /**
     * @var string
     */
    private const PARAMETER_BAG_CLASS = 'Symfony\\Component\\DependencyInjection\\ParameterBag\\ParameterBagInterface';
    public function __construct(AutowiredParamFactory $autowiredParamFactory)
    {
        $this->autowiredParamFactory = $autowiredParamFactory;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Change explicit configuration parameter pass into #[Autowire] attributes', [new CodeSample(<<<'CODE_SAMPLE'
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

final class CertificateFactory
{
    private ?string $certName;

    public function __construct(
        ParameterBagInterface $parameterBag
    ) {
        $this->certName = $parameterBag->get('certificate_name');
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use Symfony\Component\DependencyInjection\Attribute\Autowire;

final class CertificateFactory
{
    private ?string $certName;

    public function __construct(
        #[Autowire(param: 'certificate_name')]
        $certName,
    ) {
        $this->certName = $certName;
    }
}
CODE_SAMPLE
)]);
    }
    public function getNodeTypes() : array
    {
        return [Class_::class];
    }
    /**
     * @param Class_ $node
     */
    public function refactor(Node $node) : ?Class_
    {
        if ($node->isAnonymous()) {
            return null;
        }
        $constructClassMethod = $node->getMethod(MethodName::CONSTRUCT);
        if (!$constructClassMethod instanceof ClassMethod) {
            return null;
        }
        // has parameter bag interface dependency?
        if (!$this->hasParameterBagInterfaceDependency($constructClassMethod)) {
            return null;
        }
        $extraParams = [];
        $parameterBagCallCount = 0;
        // replace all parameter bag interface "->get(...)" with #[Autowire] attributes
        $this->traverseNodesWithCallable((array) $constructClassMethod->stmts, function (Node $node) use(&$extraParams, &$parameterBagCallCount) : ?Variable {
            if (!$node instanceof MethodCall) {
                return null;
            }
            if (!$this->isObjectType($node->var, new ObjectType(self::PARAMETER_BAG_CLASS))) {
                return null;
            }
            ++$parameterBagCallCount;
            if (!$this->isName($node->name, 'get')) {
                return null;
            }
            $firstArg = $node->getArgs()[0];
            $argValue = $firstArg->value;
            if (!$argValue instanceof String_) {
                return null;
            }
            // from underscore to camelcase
            $variableName = \lcfirst(\str_replace('_', '', \ucwords($argValue->value, '_')));
            $extraParams[] = $this->autowiredParamFactory->create($variableName, $argValue);
            return new Variable($variableName);
        });
        if ($extraParams === []) {
            return null;
        }
        $this->removeParameterBagParamIfNotUsed($constructClassMethod, $extraParams, $parameterBagCallCount);
        $constructClassMethod->params = \array_merge($constructClassMethod->params, $extraParams);
        return $node;
    }
    public function provideMinPhpVersion() : int
    {
        return PhpVersionFeature::ATTRIBUTES;
    }
    private function hasParameterBagInterfaceDependency(ClassMethod $classMethod) : bool
    {
        foreach ($classMethod->getParams() as $param) {
            if ($this->isParameterBagParam($param)) {
                return \true;
            }
        }
        return \false;
    }
    /**
     * @param Param[] $extraParams
     */
    private function removeParameterBagParamIfNotUsed(ClassMethod $constructClassMethod, array $extraParams, int $parameterBagCallCount) : void
    {
        // all usages of ParameterBagInterface were replaced
        if (\count($extraParams) !== $parameterBagCallCount) {
            return;
        }
        foreach ($constructClassMethod->params as $key => $param) {
            if (!$this->isParameterBagParam($param)) {
                continue;
            }
            unset($constructClassMethod->params[$key]);
        }
    }
    private function isParameterBagParam(Param $param) : bool
    {
        if (!$param->type instanceof Node) {
            return \false;
        }
        return $this->isName($param->type, self::PARAMETER_BAG_CLASS);
    }
}
