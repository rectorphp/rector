<?php

declare(strict_types=1);

namespace Rector\NetteToSymfony\Rector\MethodCall;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Identifier;
use Rector\Core\NodeManipulator\ClassMethodManipulator;
use Rector\Core\Rector\AbstractRector;
use Symfony\Component\HttpFoundation\Request;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

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

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Changes getHeader() to $request->headers->get()',
            [
                new CodeSample(
                    <<<'CODE_SAMPLE'
use Nette\Request;

final class SomeController
{
    public static function someAction(Request $request)
    {
        $header = $this->httpRequest->getHeader('x');
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
        $header = $request->headers->get('x');
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

        if (! $this->isName($node->name, 'getHeader')) {
            return null;
        }

        $requestName = $this->classMethodManipulator->addMethodParameterIfMissing(
            $node,
            Request::class,
            ['request', 'symfonyRequest']
        );

        $variable = new Variable($requestName);
        $headersPropertyFetch = new PropertyFetch($variable, 'headers');

        $node->var = $headersPropertyFetch;
        $node->name = new Identifier('get');

        return $node;
    }
}
