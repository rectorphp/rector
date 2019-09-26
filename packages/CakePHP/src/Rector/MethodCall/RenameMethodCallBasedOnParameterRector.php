<?php declare(strict_types=1);

namespace Rector\CakePHP\Rector\MethodCall;

use PhpParser\Node;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Identifier;
use PhpParser\Node\Scalar\String_;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;

/**
 * @see https://book.cakephp.org/4.0/en/appendices/4-0-migration-guide.html
 * @see https://github.com/cakephp/cakephp/commit/77017145961bb697b4256040b947029259f66a9b
 * @see \Rector\CakePHP\Tests\Rector\MethodCall\RenameMethodCallBasedOnParameterRector\RenameMethodCallBasedOnParameterRectorTest
 */
final class RenameMethodCallBasedOnParameterRector extends AbstractRector
{
    /**
     * @var mixed[]
     */
    private $methodNamesByTypes = [];

    /**
     * @param mixed[] $methodNamesByTypes
     */
    public function __construct(array $methodNamesByTypes = [])
    {
        $this->methodNamesByTypes = $methodNamesByTypes;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition(
            'Changes method calls based on matching the first parameter value.',
            [
                new CodeSample(
                    <<<'PHP'
$object = new ServerRequest();

$config = $object->getParam('paging');
$object = $object->withParam('paging', ['a value']);
PHP
                    ,
                    <<<'PHP'
$object = new ServerRequest();

$config = $object->getAttribute('paging');
$object = $object->withParam('paging', ['a value']);
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
        $config = $this->matchTypeAndMethodName($node);
        if ($config === null) {
            return null;
        }

        $node->name = new Identifier($config['replaceWith']);

        return $node;
    }

    /**
     * @return string[]
     */
    private function matchTypeAndMethodName(MethodCall $methodCall): ?array
    {
        foreach ($this->methodNamesByTypes as $type => $methodMapping) {
            /** @var string[] $methodNames */
            $methodNames = array_keys($methodMapping);
            if (! $this->isObjectType($methodCall, $type)) {
                continue;
            }

            if (! $this->isNames($methodCall, $methodNames)) {
                continue;
            }

            $currentMethodName = $this->getName($methodCall);
            if ($currentMethodName === null) {
                continue;
            }
            $config = $methodMapping[$currentMethodName];
            if (empty($config['matchParameter'])) {
                continue;
            }
            if (count($methodCall->args) < 1) {
                continue;
            }
            $arg = $methodCall->args[0];
            if (! ($arg->value instanceof String_)) {
                continue;
            }
            if ($arg->value->value !== $config['matchParameter']) {
                continue;
            }

            return $config;
        }

        return null;
    }

    /**
     * @param mixed[] $config
     */
    private function resolveNewMethodNameByCondition(MethodCall $methodCall, array $config): string
    {
        if (count($methodCall->args) >= $config['minimal_argument_count']) {
            return $config['set'];
        }

        if (! isset($methodCall->args[0])) {
            return $config['get'];
        }

        // first argument type that is considered setter
        if (! isset($config['first_argument_type_to_set'])) {
            return $config['get'];
        }

        $argumentType = $config['first_argument_type_to_set'];
        $argumentValue = $methodCall->args[0]->value;

        if ($argumentType === 'array' && $argumentValue instanceof Array_) {
            return $config['set'];
        }

        return $config['get'];
    }
}
