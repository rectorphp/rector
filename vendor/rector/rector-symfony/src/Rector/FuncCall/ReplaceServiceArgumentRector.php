<?php

declare (strict_types=1);
namespace Rector\Symfony\Rector\FuncCall;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Scalar\String_;
use Rector\Core\Contract\Rector\ConfigurableRectorInterface;
use Rector\Core\Rector\AbstractRector;
use Rector\Symfony\ValueObject\ReplaceServiceArgument;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
use RectorPrefix20220418\Webmozart\Assert\Assert;
/**
 * @see \Rector\Symfony\Tests\Rector\FuncCall\ReplaceServiceArgumentRector\ReplaceServiceArgumentRectorTest
 */
final class ReplaceServiceArgumentRector extends \Rector\Core\Rector\AbstractRector implements \Rector\Core\Contract\Rector\ConfigurableRectorInterface
{
    /**
     * @var ReplaceServiceArgument[]
     */
    private $replaceServiceArguments = [];
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Replace defined service() argument in Symfony PHP config', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample(<<<'CODE_SAMPLE'
use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

return service(ContainerInterface::class);
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

return service('service_container');
CODE_SAMPLE
, [new \Rector\Symfony\ValueObject\ReplaceServiceArgument('ContainerInterface', new \PhpParser\Node\Scalar\String_('service_container'))])]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [\PhpParser\Node\Expr\FuncCall::class];
    }
    /**
     * @param FuncCall $node
     */
    public function refactor(\PhpParser\Node $node)
    {
        if (!$this->isName($node, 'Symfony\\Component\\DependencyInjection\\Loader\\Configurator\\service')) {
            return null;
        }
        $firstArg = $node->args[0];
        if (!$firstArg instanceof \PhpParser\Node\Arg) {
            return null;
        }
        foreach ($this->replaceServiceArguments as $replaceServiceArgument) {
            if (!$this->valueResolver->isValue($firstArg->value, $replaceServiceArgument->getOldValue())) {
                continue;
            }
            $node->args[0] = new \PhpParser\Node\Arg($replaceServiceArgument->getNewValueExpr());
            return $node;
        }
        return null;
    }
    /**
     * @param mixed[] $configuration
     */
    public function configure(array $configuration) : void
    {
        \RectorPrefix20220418\Webmozart\Assert\Assert::allIsAOf($configuration, \Rector\Symfony\ValueObject\ReplaceServiceArgument::class);
        $this->replaceServiceArguments = $configuration;
    }
}
