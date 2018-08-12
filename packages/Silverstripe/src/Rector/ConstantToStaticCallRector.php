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

    public function getNodeType(): string
    {
        return ConstFetch::class;
    }

    /**
     * @param ConstFetch $constFetchNode
     */
    public function refactor(Node $constFetchNode): ?Node
    {
        // @todo make configurable
        if (! Strings::startsWith($constFetchNode->name->toString(), 'SS_')) {
            return $constFetchNode;
        }

        // @todo make configurable
        $staticCallNode = new StaticCall(new FullyQualified('Environment'), 'getEnv');
        $staticCallNode->args[] = new Arg(new String_($constFetchNode->name->toString()));

        return $staticCallNode;
    }
}
