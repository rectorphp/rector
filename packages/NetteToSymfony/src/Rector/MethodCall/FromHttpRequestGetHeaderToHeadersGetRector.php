<?php declare(strict_types=1);

namespace Rector\NetteToSymfony\Rector\MethodCall;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\ClassMethod;
use Rector\Exception\ShouldNotHappenException;
use Rector\NodeTypeResolver\Node\Attribute;
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
     * @var string
     */
    private $symfonyRequestClass = 'Symfony\Component\HttpFoundation\Request';

    public function __construct(string $netteHttpRequestClass = 'Nette\Http\Request')
    {
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

        $requestName = $this->completeRequestParameterIfMissingAndGetRequestName($node);

        $requestVariableNode = new Variable($requestName);
        $headersPropertyFetch = new PropertyFetch($requestVariableNode, 'headers');

        $node->var = $headersPropertyFetch;
        $node->name = new Identifier('get');


        return $node;
    }

    private function completeRequestParameterIfMissingAndGetRequestName(Node $node): string
    {
        $classMethodNode = $node->getAttribute(Attribute::METHOD_NODE);
        if (! $classMethodNode instanceof ClassMethod) {
            throw new ShouldNotHappenException();
        }

        foreach ($classMethodNode->params as $paramNode) {
            if ($this->isType($paramNode, $this->symfonyRequestClass)) {
                return $this->getName($paramNode);
            }
        }

        $requestName = 'request';
        foreach ($classMethodNode->params as $paramNode) {
            if ($this->isName($paramNode, 'request')) {
                $requestName = 'symfonyRequest';
            }
        }

        $classMethodNode->params[] = new Param(new Variable($requestName), null, new FullyQualified($this->symfonyRequestClass));

        return $requestName;
    }
}
