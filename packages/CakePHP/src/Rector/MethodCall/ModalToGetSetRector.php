<?php declare(strict_types=1);

namespace Rector\CakePHP\Rector\MethodCall;

use PhpParser\Node;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Identifier;
use Rector\NodeAnalyzer\MethodCallAnalyzer;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;

/**
 * @see https://book.cakephp.org/3.0/en/appendices/3-4-migration-guide.html#deprecated-combined-get-set-methods
 * @see https://github.com/cakephp/cakephp/commit/326292688c5e6d08945a3cafa4b6ffb33e714eea#diff-e7c0f0d636ca50a0350e9be316d8b0f9
 */
final class ModalToGetSetRector extends AbstractRector
{
    /**
     * @var MethodCallAnalyzer
     */
    private $methodCallAnalyzer;

    /**
     * @var mixed[]
     */
    private $methodNamesByTypes = [];

    /**
     * @param mixed[] $methodNamesByTypes
     */
    public function __construct(MethodCallAnalyzer $methodCallAnalyzer, array $methodNamesByTypes)
    {
        $this->methodCallAnalyzer = $methodCallAnalyzer;
        $this->methodNamesByTypes = $methodNamesByTypes;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition(
            'Changes combined set/get `value()` to specific `getValue()` or `setValue(x)`.',
            [
                new CodeSample(
                    <<<'CODE_SAMPLE'
$object = new InstanceConfigTrait;

$config = $object->config();
$config = $object->config('key');

$object->config('key', 'value');
$object->config(['key' => 'value']);
CODE_SAMPLE
                    ,
                    <<<'CODE_SAMPLE'
$object = new InstanceConfigTrait;

$config = $object->getConfig();
$config = $object->getConfig('key');

$object->setConfig('key', 'value');
$object->setConfig(['key' => 'value']);
CODE_SAMPLE
                ),
            ]
        );
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [MethodCall::class];
    }

    /**
     * @param MethodCall $methodCallNode
     */
    public function refactor(Node $methodCallNode): ?Node
    {
        if (! $this->isTypeAndMethodsMatch($methodCallNode)) {
            return null;
        }

        $methodName = $methodCallNode->name->toString();

        // @todo important, maybe unique condition
        if (count(
            $methodCallNode->args
        ) >= 2 || isset($methodCallNode->args[0]) && $methodCallNode->args[0]->value instanceof Array_) {
            $methodCallNode->name = new Identifier('set' . ucfirst($methodName));
        } else {
            $methodCallNode->name = new Identifier('get' . ucfirst($methodName));
        }

        return $methodCallNode;
    }

    private function isTypeAndMethodsMatch(MethodCall $methodCallNode): bool
    {
        foreach ($this->methodNamesByTypes as $type => $methodNames) {
            if ($this->methodCallAnalyzer->isTypeAndMethods($methodCallNode, $type, $methodNames)) {
                return true;
            }
        }

        return false;
    }
}
