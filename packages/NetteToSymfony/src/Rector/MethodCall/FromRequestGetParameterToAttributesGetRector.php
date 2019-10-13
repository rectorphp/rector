<?php

declare(strict_types=1);

namespace Rector\NetteToSymfony\Rector\MethodCall;

use Nette\Application\Request;
use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Identifier;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;

/**
 * @see https://doc.nette.org/en/2.4/http-request-response
 * @see https://github.com/symfony/symfony/blob/master/src/Symfony/Component/HttpFoundation/Request.php
 * @see \Rector\NetteToSymfony\Tests\Rector\MethodCall\FromRequestGetParameterToAttributesGetRector\FromRequestGetParameterToAttributesGetRectorTest
 */
final class FromRequestGetParameterToAttributesGetRector extends AbstractRector
{
    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Changes "getParameter()" to "attributes->get()" from Nette to Symfony', [
            new CodeSample(
                <<<'PHP'
use Nette\Request;

final class SomeController
{
    public static function someAction(Request $request)
    {
        $value = $request->getParameter('abz');
    }
}
PHP
                ,
                <<<'PHP'
use Nette\Request;

final class SomeController
{
    public static function someAction(Request $request)
    {
        $value = $request->attribute->get('abz');
    }
}
PHP
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
        if (! $this->isObjectType($node, Request::class)) {
            return null;
        }

        if (! $this->isName($node, 'getParameter')) {
            return null;
        }

        $requestAttributesPropertyFetch = new PropertyFetch($node->var, 'attributes');
        $node->var = $requestAttributesPropertyFetch;

        $node->name = new Identifier('get');

        return $node;
    }
}
