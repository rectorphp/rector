<?php

declare(strict_types=1);

namespace Rector\CakePHP\Rector\MethodCall;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Identifier;
use Rector\CakePHP\ValueObject\CallWithParamRename;
use Rector\Core\Contract\Rector\ConfigurableRectorInterface;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\ConfiguredCodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;
use Webmozart\Assert\Assert;

/**
 * @see https://book.cakephp.org/4.0/en/appendices/4-0-migration-guide.html
 * @see https://github.com/cakephp/cakephp/commit/77017145961bb697b4256040b947029259f66a9b
 *
 * @see \Rector\CakePHP\Tests\Rector\MethodCall\RenameMethodCallBasedOnParameterRector\RenameMethodCallBasedOnParameterRectorTest
 */
final class RenameMethodCallBasedOnParameterRector extends AbstractRector implements ConfigurableRectorInterface
{
    /**
     * @var string
     */
    public const CALLS_WITH_PARAM_RENAMES = 'calls_with_param_renames';

    /**
     * @var CallWithParamRename[]
     */
    private $callsWithParamRenames = [];

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
                        self::CALLS_WITH_PARAM_RENAMES => [
                            new CallWithParamRename('ServerRequest', 'getParam', 'paging', 'getAttribute'),
                            new CallWithParamRename('ServerRequest', 'withParam', 'paging', 'withAttribute'),
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
        $callWithParamRename = $this->matchTypeAndMethodName($node);
        if ($callWithParamRename === null) {
            return null;
        }

        $node->name = new Identifier($callWithParamRename->getNewMethod());

        return $node;
    }

    public function configure(array $configuration): void
    {
        $callsWithParamNames = $configuration[self::CALLS_WITH_PARAM_RENAMES] ?? [];
        Assert::allIsInstanceOf($callsWithParamNames, CallWithParamRename::class);
        $this->callsWithParamRenames = $callsWithParamNames;
    }

    private function matchTypeAndMethodName(MethodCall $methodCall): ?CallWithParamRename
    {
        foreach ($this->callsWithParamRenames as $callWithParamRename) {
            if (! $this->isObjectType($methodCall, $callWithParamRename->getOldClass())) {
                continue;
            }

            if (! $this->isName($methodCall->name, $callWithParamRename->getOldMethod())) {
                continue;
            }

            if (count($methodCall->args) < 1) {
                continue;
            }

            $arg = $methodCall->args[0];
            if (! $this->isValue($arg->value, $callWithParamRename->getParameterName())) {
                continue;
            }

            return $callWithParamRename;
        }

        return null;
    }
}
