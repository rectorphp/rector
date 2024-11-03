<?php

declare (strict_types=1);
namespace Rector\Symfony\Symfony60\Rector\FuncCall;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Scalar\String_;
use Rector\Contract\Rector\ConfigurableRectorInterface;
use Rector\PhpParser\Node\Value\ValueResolver;
use Rector\Rector\AbstractRector;
use Rector\Symfony\ValueObject\ReplaceServiceArgument;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
use RectorPrefix202411\Webmozart\Assert\Assert;
/**
 * @see \Rector\Symfony\Tests\Symfony60\Rector\FuncCall\ReplaceServiceArgumentRector\ReplaceServiceArgumentRectorTest
 */
final class ReplaceServiceArgumentRector extends AbstractRector implements ConfigurableRectorInterface
{
    /**
     * @readonly
     * @var \Rector\PhpParser\Node\Value\ValueResolver
     */
    private $valueResolver;
    /**
     * @var ReplaceServiceArgument[]
     */
    private $replaceServiceArguments = [];
    public function __construct(ValueResolver $valueResolver)
    {
        $this->valueResolver = $valueResolver;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Replace defined service() argument in Symfony PHP config', [new ConfiguredCodeSample(<<<'CODE_SAMPLE'
use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

return service(ContainerInterface::class);
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

return service('service_container');
CODE_SAMPLE
, [new ReplaceServiceArgument('ContainerInterface', new String_('service_container'))])]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [FuncCall::class];
    }
    /**
     * @param FuncCall $node
     */
    public function refactor(Node $node) : ?FuncCall
    {
        if (!$this->isName($node, 'Symfony\\Component\\DependencyInjection\\Loader\\Configurator\\service')) {
            return null;
        }
        $firstArg = $node->args[0];
        if (!$firstArg instanceof Arg) {
            return null;
        }
        foreach ($this->replaceServiceArguments as $replaceServiceArgument) {
            if (!$this->valueResolver->isValue($firstArg->value, $replaceServiceArgument->getOldValue())) {
                continue;
            }
            $node->args[0] = new Arg($replaceServiceArgument->getNewValueExpr());
            return $node;
        }
        return null;
    }
    /**
     * @param mixed[] $configuration
     */
    public function configure(array $configuration) : void
    {
        Assert::allIsAOf($configuration, ReplaceServiceArgument::class);
        $this->replaceServiceArguments = $configuration;
    }
}
