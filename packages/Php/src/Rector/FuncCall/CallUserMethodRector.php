<?php declare(strict_types=1);

namespace Rector\Php\Rector\FuncCall;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Name;
use Rector\Node\NodeFactory;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;

final class CallUserMethodRector extends AbstractRector
{
    /**
     * @var NodeFactory
     */
    private $nodeFactory;

    /**
     * @var string[]
     */
    private $oldToNewFunctions = [
        'call_user_method' => 'call_user_func',
        'call_user_method_array' => 'call_user_func_array',
    ];

    public function __construct(NodeFactory $nodeFactory)
    {
        $this->nodeFactory = $nodeFactory;
    }

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
     * @param FuncCall $funcCallNode
     */
    public function refactor(Node $funcCallNode): ?Node
    {
        $newName = $this->matchNewFunctionName($funcCallNode);
        if ($newName === null) {
            return $funcCallNode;
        }

        $funcCallNode->name = new Name($newName);

        $argNodes = $funcCallNode->args;

        $funcCallNode->args[0] = new Arg($this->nodeFactory->createArray($argNodes[1]->value, $argNodes[0]->value));
        unset($funcCallNode->args[1]);

        // reindex from 0
        $funcCallNode->args = array_values($funcCallNode->args);

        return $funcCallNode;
    }

    private function matchNewFunctionName(FuncCall $funcCallNode): ?string
    {
        foreach ($this->oldToNewFunctions as $oldFunction => $newFunction) {
            if ((string) $funcCallNode->name === $oldFunction) {
                return $newFunction;
            }
        }

        return null;
    }
}
