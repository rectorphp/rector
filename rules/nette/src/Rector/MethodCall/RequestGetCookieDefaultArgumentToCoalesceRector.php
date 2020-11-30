<?php

declare(strict_types=1);

namespace Rector\Nette\Rector\MethodCall;

use PhpParser\Node;
use PhpParser\Node\Expr\BinaryOp\Coalesce;
use PhpParser\Node\Expr\MethodCall;
use Rector\Core\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @sponsor Thanks https://amateri.com for sponsoring this rule - visit them on https://www.startupjobs.cz/startup/scrumworks-s-r-o
 *
 * @see \Rector\Nette\Tests\Rector\MethodCall\RequestGetCookieDefaultArgumentToCoalesceRector\RequestGetCookieDefaultArgumentToCoalesceRectorTest
 */
final class RequestGetCookieDefaultArgumentToCoalesceRector extends AbstractRector
{
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Add removed Nette\Http\Request::getCookies() default value as coalesce',
            [
                new CodeSample(
                    <<<'CODE_SAMPLE'
use Nette\Http\Request;

class SomeClass
{
    public function run(Request $request)
    {
        return $request->getCookie('name', 'default');
    }
}
CODE_SAMPLE

                    ,
                    <<<'CODE_SAMPLE'
use Nette\Http\Request;

class SomeClass
{
    public function run(Request $request)
    {
        return $request->getCookie('name') ?? 'default';
    }
}
CODE_SAMPLE

                ),

            ]);
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
        if (! $this->isObjectType($node->var, 'Nette\Http\Request')) {
            return null;
        }

        if (! $this->isName($node->name, 'getCookie')) {
            return null;
        }

        // no default value
        if (! isset($node->args[1])) {
            return null;
        }

        $defaultValue = $node->args[1]->value;
        unset($node->args[1]);

        return new Coalesce($node, $defaultValue);
    }
}
