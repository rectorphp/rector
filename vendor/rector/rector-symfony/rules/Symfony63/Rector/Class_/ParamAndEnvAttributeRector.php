<?php

declare (strict_types=1);
namespace Rector\Symfony\Symfony63\Rector\Class_;

use RectorPrefix202411\Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Identifier;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use Rector\Rector\AbstractRector;
use Rector\Symfony\Enum\SymfonyAttribute;
use Rector\ValueObject\MethodName;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Symfony\Tests\Symfony63\Rector\Class_\ParamAndEnvAttributeRector\ParamAndEnvAttributeRectorTest
 *
 * @see https://symfony.com/blog/new-in-symfony-6-3-dependency-injection-improvements#new-options-for-autowire-attribute
 */
final class ParamAndEnvAttributeRector extends AbstractRector
{
    /**
     * @var string
     * @see https://regex101.com/r/7vwGbH/1
     */
    private const PARAMETER_REGEX = '#%(?<param>[\\w\\.]+)%$#';
    /**
     * @var string
     * @see https://regex101.com/r/7xpVRP/1
     */
    private const ENV_REGEX = '#%env\\((?<env>\\w+)\\)%$#';
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Make param/env use in #[Attribute] more precise', [new CodeSample(<<<'CODE_SAMPLE'
namespace App\Service;

use Symfony\Component\DependencyInjection\Attribute\Autowire;

class MessageGenerator
{
    public function __construct(
        #[Autowire('%kernel.debug%')]
        bool $debugMode,

        #[Autowire('%env(SOME_ENV_VAR)%')]
        string $senderName,
    ) {
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
namespace App\Service;

use Symfony\Component\DependencyInjection\Attribute\Autowire;

class MessageGenerator
{
    public function __construct(
        #[Autowire(param: 'kernel.debug')]
        bool $debugMode,

        #[Autowire(env: 'SOME_ENV_VAR')]
        string $senderName,
    ) {
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
    public function refactor(Node $node) : ?Node
    {
        $classMethod = $node->getMethod(MethodName::CONSTRUCT);
        if (!$classMethod instanceof ClassMethod) {
            return null;
        }
        if ($classMethod->getParams() === []) {
            return null;
        }
        $hasChanged = \false;
        foreach ($classMethod->params as $param) {
            foreach ($param->attrGroups as $attrGroup) {
                foreach ($attrGroup->attrs as $attribute) {
                    if (!$this->isName($attribute->name, SymfonyAttribute::AUTOWIRE)) {
                        continue;
                    }
                    foreach ($attribute->args as $attributeArg) {
                        if ($this->isAlreadyEnvParamNamed($attributeArg)) {
                            continue;
                        }
                        // we can handle only string values
                        if (!$attributeArg->value instanceof String_) {
                            continue;
                        }
                        $envMatch = Strings::match($attributeArg->value->value, self::ENV_REGEX);
                        if (isset($envMatch['env'])) {
                            $attributeArg->name = new Identifier('env');
                            $attributeArg->value = new String_($envMatch['env']);
                            $hasChanged = \true;
                            continue;
                        }
                        $paramMatch = Strings::match($attributeArg->value->value, self::PARAMETER_REGEX);
                        if (isset($paramMatch['param'])) {
                            $attributeArg->name = new Identifier('param');
                            $attributeArg->value = new String_($paramMatch['param']);
                            $hasChanged = \true;
                        }
                    }
                }
            }
        }
        if ($hasChanged) {
            return $node;
        }
        return null;
    }
    private function isAlreadyEnvParamNamed(Arg $arg) : bool
    {
        if (!$arg->name instanceof Identifier) {
            return \false;
        }
        return \in_array($arg->name->toString(), ['env', 'param'], \true);
    }
}
