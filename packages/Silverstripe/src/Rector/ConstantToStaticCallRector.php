<?php declare(strict_types=1);

namespace Rector\Silverstripe\Rector;

use Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\ConstFetch;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Scalar\String_;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;

final class ConstantToStaticCallRector extends AbstractRector
{
    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Turns defined constant to static method call.', [
            new CodeSample('SS_DATABASE_NAME;', 'Environment::getEnv("SS_DATABASE_NAME");'),
        ]);
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [ConstFetch::class];
    }

    /**
     * @param ConstFetch $node
     */
    public function refactor(Node $node): ?Node
    {
        if (! Strings::startsWith($node->name->toString(), 'SS_')) {
            return null;
        }

        $staticCallNode = new StaticCall(new FullyQualified('Environment'), 'getEnv');
        $staticCallNode->args[] = new Arg(new String_($node->name->toString()));

        return $staticCallNode;
    }
}
