<?php

declare(strict_types=1);

namespace Rector\Php70\Rector\FuncCall;

use PhpParser\Node;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Name;
use Rector\Core\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @see \Rector\Php70\Tests\Rector\FuncCall\CallUserMethodRector\CallUserMethodRectorTest
 */
final class CallUserMethodRector extends AbstractRector
{
    /**
     * @var array<string, string>
     */
    private const OLD_TO_NEW_FUNCTIONS = [
        'call_user_method' => 'call_user_func',
        'call_user_method_array' => 'call_user_func_array',
    ];

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Changes call_user_method()/call_user_method_array() to call_user_func()/call_user_func_array()',
            [
                new CodeSample(
                    'call_user_method($method, $obj, "arg1", "arg2");',
                    'call_user_func(array(&$obj, "method"), "arg1", "arg2");'
                ),
            ]
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
        $oldFunctionNames = array_keys(self::OLD_TO_NEW_FUNCTIONS);
        if (! $this->isNames($node, $oldFunctionNames)) {
            return null;
        }

        $newName = self::OLD_TO_NEW_FUNCTIONS[$this->getName($node)];
        $node->name = new Name($newName);

        $oldArgs = $node->args;

        unset($node->args[1]);

        $newArgs = [$this->nodeFactory->createArg([$oldArgs[1]->value, $oldArgs[0]->value])];

        unset($oldArgs[0]);
        unset($oldArgs[1]);

        $node->args = $this->appendArgs($newArgs, $oldArgs);

        return $node;
    }
}
