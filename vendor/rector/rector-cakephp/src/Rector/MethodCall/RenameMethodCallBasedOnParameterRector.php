<?php

declare (strict_types=1);
namespace Rector\CakePHP\Rector\MethodCall;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Identifier;
use Rector\CakePHP\ValueObject\RenameMethodCallBasedOnParameter;
use Rector\Core\Contract\Rector\ConfigurableRectorInterface;
use Rector\Core\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
use RectorPrefix20220418\Webmozart\Assert\Assert;
/**
 * @see https://book.cakephp.org/4.0/en/appendices/4-0-migration-guide.html
 * @see https://github.com/cakephp/cakephp/commit/77017145961bb697b4256040b947029259f66a9b
 *
 * @see \Rector\CakePHP\Tests\Rector\MethodCall\RenameMethodCallBasedOnParameterRector\RenameMethodCallBasedOnParameterRectorTest
 */
final class RenameMethodCallBasedOnParameterRector extends \Rector\Core\Rector\AbstractRector implements \Rector\Core\Contract\Rector\ConfigurableRectorInterface
{
    /**
     * @var string
     */
    public const CALLS_WITH_PARAM_RENAMES = 'calls_with_param_renames';
    /**
     * @var RenameMethodCallBasedOnParameter[]
     */
    private $callsWithParamRenames = [];
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        $configuration = [self::CALLS_WITH_PARAM_RENAMES => [new \Rector\CakePHP\ValueObject\RenameMethodCallBasedOnParameter('ServerRequest', 'getParam', 'paging', 'getAttribute'), new \Rector\CakePHP\ValueObject\RenameMethodCallBasedOnParameter('ServerRequest', 'withParam', 'paging', 'withAttribute')]];
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Changes method calls based on matching the first parameter value.', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample(<<<'CODE_SAMPLE'
$object = new ServerRequest();

$config = $object->getParam('paging');
$object = $object->withParam('paging', ['a value']);
CODE_SAMPLE
, <<<'CODE_SAMPLE'
$object = new ServerRequest();

$config = $object->getAttribute('paging');
$object = $object->withAttribute('paging', ['a value']);
CODE_SAMPLE
, $configuration)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [\PhpParser\Node\Expr\MethodCall::class];
    }
    /**
     * @param MethodCall $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        $callWithParamRename = $this->matchTypeAndMethodName($node);
        if (!$callWithParamRename instanceof \Rector\CakePHP\ValueObject\RenameMethodCallBasedOnParameter) {
            return null;
        }
        $node->name = new \PhpParser\Node\Identifier($callWithParamRename->getNewMethod());
        return $node;
    }
    /**
     * @param mixed[] $configuration
     */
    public function configure(array $configuration) : void
    {
        $callsWithParamRenames = $configuration[self::CALLS_WITH_PARAM_RENAMES] ?? $configuration;
        \RectorPrefix20220418\Webmozart\Assert\Assert::isArray($callsWithParamRenames);
        \RectorPrefix20220418\Webmozart\Assert\Assert::allIsInstanceOf($callsWithParamRenames, \Rector\CakePHP\ValueObject\RenameMethodCallBasedOnParameter::class);
        $this->callsWithParamRenames = $callsWithParamRenames;
    }
    private function matchTypeAndMethodName(\PhpParser\Node\Expr\MethodCall $methodCall) : ?\Rector\CakePHP\ValueObject\RenameMethodCallBasedOnParameter
    {
        if (\count($methodCall->args) < 1) {
            return null;
        }
        $firstArgValue = $methodCall->args[0]->value;
        foreach ($this->callsWithParamRenames as $callWithParamRename) {
            if (!$this->isObjectType($methodCall->var, $callWithParamRename->getOldObjectType())) {
                continue;
            }
            if (!$this->isName($methodCall->name, $callWithParamRename->getOldMethod())) {
                continue;
            }
            if (!$this->valueResolver->isValue($firstArgValue, $callWithParamRename->getParameterName())) {
                continue;
            }
            return $callWithParamRename;
        }
        return null;
    }
}
