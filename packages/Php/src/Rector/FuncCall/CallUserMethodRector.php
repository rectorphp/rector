<?php declare(strict_types=1);

namespace Rector\Php\Rector\FuncCall;

use PhpParser\Node;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Name;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;

final class CallUserMethodRector extends AbstractRector
{
    /**
     * @var string[]
     */
    private $oldToNewFunctions = [
        'call_user_method' => 'call_user_func',
        'call_user_method_array' => 'call_user_func_array',
    ];

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition(
            'Changes call_user_method()/call_user_method_array() to call_user_func()/call_user_func_array()',
            [new CodeSample(
                'call_user_method($method, $obj, "arg1", "arg2");',
                'call_user_func(array(&$obj, "method"), "arg1", "arg2");'
            )]
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
        if (! $this->isNames($node, array_keys($this->oldToNewFunctions))) {
            return null;
        }

        $newName = $this->oldToNewFunctions[$this->getName($node)];
        $node->name = new Name($newName);

        $argNodes = $node->args;

        $node->args[0] = $this->createArg([$argNodes[1]->value, $argNodes[0]->value]);
        unset($node->args[1]);

        return $node;
    }
}
