<?php declare(strict_types=1);

namespace Rector\CakePHP\Rector\MethodCall;

use PhpParser\Node;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Identifier;
use PhpParser\Node\Scalar\String_;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\ConfiguredCodeSample;
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
                new ConfiguredCodeSample(
                    <<<'PHP'
$object = new ServerRequest();

$config = $object->getParam('paging');
$object = $object->withParam('paging', ['a value']);
PHP
                    ,
                    <<<'PHP'
$object = new ServerRequest();

$config = $object->getAttribute('paging');
$object = $object->withAttribute('paging', ['a value']);
PHP
                    ,
                    [
                        'getParam' => [
                            'match_parameter' => 'paging',
                            'replace_with' => 'getAttribute'
                        ],
                        'withParam' => [
                            'match_parameter' => 'paging',
                            'replace_with' => 'withAttribute'
                        ]
                    ]
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

        $node->name = new Identifier($config['replace_with']);

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
            if (empty($config['match_parameter'])) {
                continue;
            }
            if (count($methodCall->args) < 1) {
                continue;
            }
            $arg = $methodCall->args[0];
            if (! ($arg->value instanceof String_)) {
                continue;
            }
            if ($arg->value->value !== $config['match_parameter']) {
                continue;
            }

            return $config;
        }

        return null;
    }
}
