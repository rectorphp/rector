<?php

declare(strict_types=1);

namespace Rector\Core\Rector\FuncCall;

use PhpParser\Node;
use PhpParser\Node\Expr\FuncCall;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\ConfiguredCodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;

/**
 * @sponsor Thanks https://twitter.com/afilina for sponsoring this rule
 *
 * @see \Rector\Core\Tests\Rector\FuncCall\RemoveIniGetSetFuncCallRector\RemoveIniGetSetFuncCallRectorTest
 */
final class RemoveIniGetSetFuncCallRector extends AbstractRector
{
    /**
     * @var string[]
     */
    private $keysToRemove = [];

    /**
     * @param string[] $keysToRemove
     */
    public function __construct(array $keysToRemove = [])
    {
        $this->keysToRemove = $keysToRemove;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Remove ini_get by configuration', [
            new ConfiguredCodeSample(
                <<<'CODE_SAMPLE'
ini_get('y2k_compliance');
ini_set('y2k_compliance', 1);
CODE_SAMPLE
                ,
                '',
                [
                    '$keysToRemove' => ['y2k_compliance'],
                ]
            ), ]
        );
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [FuncCall::class];
    }

    /**
     * @param FuncCall $node
     */
    public function refactor(Node $node): ?Node
    {
        if (! $this->isNames($node, ['ini_get', 'ini_set'])) {
            return null;
        }

        if (! isset($node->args[0])) {
            return null;
        }

        $keyValue = $this->getValue($node->args[0]->value);
        if (! in_array($keyValue, $this->keysToRemove, true)) {
            return null;
        }

        $this->removeNode($node);

        return null;
    }
}
