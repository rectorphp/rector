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
            /** @var string[] $methodNames */
            $methodNames = array_keys($methodNamesToGetAndSetNames);
            if (! $this->methodCallAnalyzer->isTypeAndMethods($methodCallNode, $type, $methodNames)) {
                continue;
            }

            $currentMethodName = (string) $methodCallNode->name;
            $config = $methodNamesToGetAndSetNames[$currentMethodName];

            // default
            $config['set'] = $config['set'] ?? 'set' . ucfirst($currentMethodName);
            $config['get'] = $config['get'] ?? 'get' . ucfirst($currentMethodName);

            // default minimal argument count for setter
            $config['minimal_argument_count'] = $config['minimal_argument_count'] ?? 1;

            return $config;
        }

        return null;
    }

    /**
     * @param mixed[] $config
     */
    private function resolveNewMethodNameByCondition(MethodCall $methodCallNode, array $config): string
    {
        if (count($methodCallNode->args) >= $config['minimal_argument_count']) {
            return $config['set'];
        }

        if (isset($methodCallNode->args[0])) {
            // first argument type that is considered setter
            if (isset($config['first_argument_type_to_set'])) {
                $argumentType = $config['first_argument_type_to_set'];
                $argumentValue = $methodCallNode->args[0]->value;

                if ($argumentType === 'array' && $argumentValue instanceof Array_) {
                    return $config['set'];
                }
            }
        }

        return $config['get'];
    }
}
