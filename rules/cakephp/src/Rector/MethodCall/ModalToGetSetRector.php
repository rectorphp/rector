<?php

declare(strict_types=1);

namespace Rector\CakePHP\Rector\MethodCall;

use PhpParser\Node;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Identifier;
use Rector\Core\Contract\Rector\ConfigurableRectorInterface;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;

/**
 * @see https://book.cakephp.org/3.0/en/appendices/3-4-migration-guide.html#deprecated-combined-get-set-methods
 * @see https://github.com/cakephp/cakephp/commit/326292688c5e6d08945a3cafa4b6ffb33e714eea#diff-e7c0f0d636ca50a0350e9be316d8b0f9
 * @see \Rector\CakePHP\Tests\Rector\MethodCall\ModalToGetSetRector\ModalToGetSetRectorTest
 */
final class ModalToGetSetRector extends AbstractRector implements ConfigurableRectorInterface
{
    /**
     * @var string
     */
    public const METHOD_NAMES_BY_TYPES = '$methodNamesByTypes';

    /**
     * @var string
     */
    private const SET = 'set';

    /**
     * @var string
     */
    private const GET = 'get';

    /**
     * @var string
     */
    private const MINIMAL_ARGUMENT_COUNT = 'minimal_argument_count';

    /**
     * @var mixed[]
     */
    private $methodNamesByTypes = [];

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition(
            'Changes combined set/get `value()` to specific `getValue()` or `setValue(x)`.',
            [
                new CodeSample(
                    <<<'PHP'
$object = new InstanceConfigTrait;

$config = $object->config();
$config = $object->config('key');

$object->config('key', 'value');
$object->config(['key' => 'value']);
PHP
                    ,
                    <<<'PHP'
$object = new InstanceConfigTrait;

$config = $object->getConfig();
$config = $object->getConfig('key');

$object->setConfig('key', 'value');
$object->setConfig(['key' => 'value']);
PHP
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
     * @param MethodCall $node
     */
    public function refactor(Node $node): ?Node
    {
        $typeAndMethodNames = $this->matchTypeAndMethodName($node);
        if ($typeAndMethodNames === null) {
            return null;
        }

        $newName = $this->resolveNewMethodNameByCondition($node, $typeAndMethodNames);
        $node->name = new Identifier($newName);

        return $node;
    }

    public function configure(array $configuration): void
    {
        $this->methodNamesByTypes = $configuration[self::METHOD_NAMES_BY_TYPES] ?? [];
    }

    /**
     * @return string[]
     */
    private function matchTypeAndMethodName(MethodCall $methodCall): ?array
    {
        foreach ($this->methodNamesByTypes as $type => $methodNamesToGetAndSetNames) {
            /** @var string[] $methodNames */
            $methodNames = array_keys($methodNamesToGetAndSetNames);
            if (! $this->isObjectType($methodCall->var, $type)) {
                continue;
            }

            if (! $this->isNames($methodCall->name, $methodNames)) {
                continue;
            }

            $currentMethodName = $this->getName($methodCall->name);
            if ($currentMethodName === null) {
                continue;
            }

            $config = $methodNamesToGetAndSetNames[$currentMethodName];

            // default
            $config[self::SET] = $config[self::SET] ?? self::SET . ucfirst($currentMethodName);
            $config[self::GET] = $config[self::GET] ?? self::GET . ucfirst($currentMethodName);

            // default minimal argument count for setter
            $config[self::MINIMAL_ARGUMENT_COUNT] = $config[self::MINIMAL_ARGUMENT_COUNT] ?? 1;

            return $config;
        }

        return null;
    }

    /**
     * @param mixed[] $config
     */
    private function resolveNewMethodNameByCondition(MethodCall $methodCall, array $config): string
    {
        if (count($methodCall->args) >= $config[self::MINIMAL_ARGUMENT_COUNT]) {
            return $config[self::SET];
        }

        if (! isset($methodCall->args[0])) {
            return $config[self::GET];
        }

        // first argument type that is considered setter
        if (! isset($config['first_argument_type_to_set'])) {
            return $config[self::GET];
        }

        $argumentType = $config['first_argument_type_to_set'];
        $argumentValue = $methodCall->args[0]->value;

        if ($argumentType === 'array' && $argumentValue instanceof Array_) {
            return $config[self::SET];
        }

        return $config[self::GET];
    }
}
