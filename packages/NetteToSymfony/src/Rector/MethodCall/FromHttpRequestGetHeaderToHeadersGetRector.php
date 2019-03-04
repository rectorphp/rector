<?php declare(strict_types=1);

namespace Rector\NetteToSymfony\Rector\MethodCall;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Identifier;
use Rector\PhpParser\Node\Manipulator\ClassMethodManipulator;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;

/**
 * @see https://doc.nette.org/en/2.4/http-request-response
 * @see https://github.com/symfony/symfony/blob/master/src/Symfony/Component/HttpFoundation/Request.php
 */
final class FromHttpRequestGetHeaderToHeadersGetRector extends AbstractRector
{
    /**
     * @var string
     */
    private $netteHttpRequestClass;

    /**
     * @var ClassMethodManipulator
     */
    private $classMethodManipulator;

    /**
     * @var string
     */
    private $symfonyRequestClass = 'Symfony\Component\HttpFoundation\Request';

    public function __construct(
        ClassMethodManipulator $classMethodManipulator,
        string $netteHttpRequestClass = 'Nette\Http\Request'
    ) {
        $this->classMethodManipulator = $classMethodManipulator;
        $this->netteHttpRequestClass = $netteHttpRequestClass;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Changes getHeader() to $request->headers->get()', [
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
        if (! $this->isType($node, $this->netteHttpRequestClass)) {
            return null;
        }

        if (! $this->isName($node, 'getHeader')) {
            return null;
        }

        $requestName = $this->classMethodManipulator->addMethodParameterIfMissing(
            $node,
            $this->symfonyRequestClass,
            ['request', 'symfonyRequest']
        );

        $requestVariableNode = new Variable($requestName);
        $headersPropertyFetch = new PropertyFetch($requestVariableNode, 'headers');

        $node->var = $headersPropertyFetch;
        $node->name = new Identifier('get');

        return $node;
    }
}
