<?php

declare(strict_types=1);

namespace Rector\CakePHP\Rector\MethodCall;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Identifier;
use PhpParser\Node\Scalar\String_;
use Rector\Core\Contract\Rector\ConfigurableRectorInterface;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\ConfiguredCodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;

/**
 * @see https://book.cakephp.org/4.0/en/appendices/4-0-migration-guide.html
 * @see https://github.com/cakephp/cakephp/commit/77017145961bb697b4256040b947029259f66a9b
 * @see \Rector\CakePHP\Tests\Rector\MethodCall\RenameMethodCallBasedOnParameterRector\RenameMethodCallBasedOnParameterRectorTest
 */
final class RenameMethodCallBasedOnParameterRector extends AbstractRector implements ConfigurableRectorInterface
{
    /**
     * @var string
     */
    public const METHOD_NAMES_BY_TYPES = '$methodNamesByTypes';

    /**
     * @var string
     */
    private const MATCH_PARAMETER = 'match_parameter';

    /**
     * @var string
     */
    private const REPLACE_WITH = 'replace_with';

    /**
     * @var mixed[]
     */
    private $methodNamesByTypes = [];

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
                        '$methodNamesByTypes' => [
                            'getParam' => [
                                self::MATCH_PARAMETER => 'paging',
                                self::REPLACE_WITH => 'getAttribute',
                            ],
                            'withParam' => [
                                self::MATCH_PARAMETER => 'paging',
                                self::REPLACE_WITH => 'withAttribute',
                            ],
                        ],
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

        $node->name = new Identifier($config[self::REPLACE_WITH]);

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
        foreach ($this->methodNamesByTypes as $type => $methodMapping) {
            /** @var string[] $methodNames */
            $methodNames = array_keys($methodMapping);
            if (! $this->isObjectType($methodCall, $type)) {
                continue;
            }

            if (! $this->isNames($methodCall->name, $methodNames)) {
                continue;
            }

            $currentMethodName = $this->getName($methodCall->name);
            if ($currentMethodName === null) {
                continue;
            }
            $config = $methodMapping[$currentMethodName];
            if (! isset($config[self::MATCH_PARAMETER])) {
                continue;
            }
            if (count($methodCall->args) < 1) {
                continue;
            }
            $arg = $methodCall->args[0];
            if (! ($arg->value instanceof String_)) {
                continue;
            }
            if ($arg->value->value !== $config[self::MATCH_PARAMETER]) {
                continue;
            }

            return $config;
        }

        return null;
    }
}
