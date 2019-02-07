<?php declare(strict_types=1);

namespace Rector\NetteToSymfony\Rector\MethodCall;

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
 */
final class FromRequestGetParameterToAttributesGetRector extends AbstractRector
{
    /**
     * @var string
     */
    private $netteRequestClass;

    public function __construct(string $netteRequestClass = 'Nette\Application\Request')
    {
        $this->netteRequestClass = $netteRequestClass;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Changes "getParameter()" to "attributes->get()" from Nette to Symfony', [
            new CodeSample(
                <<<'CODE_SAMPLE'
use Nette\Request;

final class SomeController
{
    public static function someAction(Request $request)
    {
        $value = $request->getParameter('abz');
    }
}
CODE_SAMPLE
                ,
                <<<'CODE_SAMPLE'
use Nette\Request;

final class SomeController
{
    public static function someAction(Request $request)
    {
        $value = $request->attribute->get('abz');
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
        if (! $this->isType($node, $this->netteRequestClass)) {
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
