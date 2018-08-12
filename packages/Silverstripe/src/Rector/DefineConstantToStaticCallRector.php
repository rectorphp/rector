<?php declare(strict_types=1);

namespace Rector\Silverstripe\Rector;

use Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Scalar\String_;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;

final class DefineConstantToStaticCallRector extends AbstractRector
{
    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Turns defined function call to static method call.', [
            new CodeSample('defined("SS_DATABASE_NAME");', 'Environment::getEnv("SS_DATABASE_NAME");'),
        ]);
    }

    public function getNodeType(): string
    {
        return FuncCall::class;
    }

    /**
     * @param FuncCall $node
     */
    public function refactor(Node $node): ?Node
    {
        if (count($node->args) !== 1) {
            return $node;
        }

        $argumentValue = $node->args[0]->value;
        if (! $argumentValue instanceof String_) {
            return $node;
        }

        // @todo make configurable
        if (! Strings::startsWith($argumentValue->value, 'SS_')) {
            return $node;
        }

        // @todo make configurable
        $staticCallNode = new StaticCall(new FullyQualified('Environment'), 'getEnv');
        $staticCallNode->args = $node->args;

        return $staticCallNode;
    }
}
