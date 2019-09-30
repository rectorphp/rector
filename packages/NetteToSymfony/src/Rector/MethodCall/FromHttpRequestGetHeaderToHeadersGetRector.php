<?php declare(strict_types=1);

namespace Rector\NetteToSymfony\Rector\MethodCall;

use Nette\Http\Request;
use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Identifier;
use Rector\PhpParser\Node\Manipulator\ClassMethodManipulator;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;
use Symfony\Component\HttpFoundation\Request as SymfonyRequest;

/**
 * @see https://doc.nette.org/en/2.4/http-request-response
 * @see https://github.com/symfony/symfony/blob/master/src/Symfony/Component/HttpFoundation/Request.php
 * @see \Rector\NetteToSymfony\Tests\Rector\MethodCall\FromHttpRequestGetHeaderToHeadersGetRector\FromHttpRequestGetHeaderToHeadersGetRectorTest
 */
final class FromHttpRequestGetHeaderToHeadersGetRector extends AbstractRector
{
    /**
     * @var ClassMethodManipulator
     */
    private $classMethodManipulator;

    public function __construct(ClassMethodManipulator $classMethodManipulator)
    {
        $this->classMethodManipulator = $classMethodManipulator;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Changes getHeader() to $request->headers->get()', [
            new CodeSample(
                <<<'PHP'
use Nette\Request;

final class SomeController
{
    public static function someAction(Request $request)
    {
        $header = $this->httpRequest->getHeader('x');
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
        $header = $request->headers->get('x');
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

        if (! $this->isName($node, 'getHeader')) {
            return null;
        }

        $requestName = $this->classMethodManipulator->addMethodParameterIfMissing(
            $node,
            SymfonyRequest::class,
            ['request', 'symfonyRequest']
        );

        $requestVariableNode = new Variable($requestName);
        $headersPropertyFetch = new PropertyFetch($requestVariableNode, 'headers');

        $node->var = $headersPropertyFetch;
        $node->name = new Identifier('get');

        return $node;
    }
}
