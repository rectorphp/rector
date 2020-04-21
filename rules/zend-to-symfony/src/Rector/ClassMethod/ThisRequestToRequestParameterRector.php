<?php

declare(strict_types=1);

namespace Rector\ZendToSymfony\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\ClassMethod;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;
use Rector\ZendToSymfony\Detector\ZendDetector;
use Symfony\Component\HttpFoundation\Request;

/**
 * @sponsor Thanks https://previo.cz/ for sponsoring this rule
 *
 * @see \Rector\ZendToSymfony\Tests\Rector\ClassMethod\ThisRequestToRequestParameterRector\ThisRequestToRequestParameterRectorTest
 */
final class ThisRequestToRequestParameterRector extends AbstractRector
{
    /**
     * @var ZendDetector
     */
    private $zendDetector;

    public function __construct(ZendDetector $zendDetector)
    {
        $this->zendDetector = $zendDetector;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Change $this->_request in action method to $request parameter', [new CodeSample(
            <<<'PHP'
public function someAction()
{
    $isGet = $this->_request->isGet();
}
PHP
            ,
            <<<'PHP'
public function someAction(\Symfony\Component\HttpFoundation\Request $request)
{
    $isGet = $request->isGet();
}
PHP
        )]);
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [ClassMethod::class];
    }

    /**
     * @param ClassMethod $node
     */
    public function refactor(Node $node): ?Node
    {
        if (! $this->zendDetector->isZendActionMethod($node)) {
            return null;
        }

        $hasRequest = false;
        $this->traverseNodesWithCallable((array) $node->stmts, function (Node $node) use (&$hasRequest): ?Variable {
            if (! $this->isLocalPropertyFetchName($node, '_request')) {
                return null;
            }

            $hasRequest = true;

            // @todo rename method call based on Zend → Symfony
            // "isXmlHttpRequest()" →
            // "isPost()" →
            // "getPosts()" →

            return new Variable('request');
        });

        // add request argument
        if (! $hasRequest) {
            return null;
        }

        $node->params[] = $this->createParamWithNameAndClassType('request', Request::class);

        return $node;
    }

    private function createParamWithNameAndClassType(string $name, string $classType): Param
    {
        return new Param(new Variable($name), null, new FullyQualified($classType));
    }
}
