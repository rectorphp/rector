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
        $typeAndMethodNames = $this->matchTypeAndMethodName($methodCallNode);
        if ($typeAndMethodNames === null) {
            return null;
        }

        // @todo important, maybe unique condition
        $newName = $this->resolveNewMethodNameByCondition($methodCallNode, $typeAndMethodNames);
        $methodCallNode->name = new Identifier($newName);

        return $methodCallNode;
    }

    /**
     * @return string[]
     */
    private function matchTypeAndMethodName(MethodCall $methodCallNode): ?array
    {
        foreach ($this->methodNamesByTypes as $type => $methodNamesToGetAndSetNames) {
            $methodNames = array_keys($methodNamesToGetAndSetNames);
            if (! $this->methodCallAnalyzer->isTypeAndMethods($methodCallNode, $type, $methodNames)) {
                continue;
            }

            $newNames = $methodNamesToGetAndSetNames[$methodCallNode->name->toString()];
            if ($newNames === null) {
                $currentMethodName = $methodCallNode->name->toString();

                return [
                   'get' => 'get' . ucfirst($currentMethodName),
                   'set' => 'set' . ucfirst($currentMethodName) ,
                ];
            }

            return $newNames;
        }

        return null;
    }

    /**
     * @param mixed[] $typeAndMethodNames
     */
    private function resolveNewMethodNameByCondition(MethodCall $methodCallNode, array $typeAndMethodNames): string
    {
        // default:
        // - has arguments? => set
        // - get
        // "minimal_argument_count" : 2
        // "first_argument_type" : array

        if (
            count($methodCallNode->args) >= 2 ||
            (isset($methodCallNode->args[0]) && $methodCallNode->args[0]->value instanceof Array_)
        ) {
            return $typeAndMethodNames['set'];
        }

        return $typeAndMethodNames['get'];
    }
}
