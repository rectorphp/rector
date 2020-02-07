<?php

declare(strict_types=1);

namespace Rector\CakePHPToSymfony\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Stmt\ClassMethod;
use Rector\CakePHPToSymfony\Rector\AbstractCakePHPRector;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;

/**
 * @see https://book.cakephp.org/2/en/tutorials-and-examples/blog/part-two.html
 * @see https://symfony.com/doc/5.0/controller.html
 * @see https://symfony.com/doc/5.0/controller.html#rendering-templates
 *
 * @see https://stackoverflow.com/a/21647715/1348344 for $this->view
 *
 * @see \Rector\CakePHPToSymfony\Tests\Rector\ClassMethod\CakePHPControllerActionToSymfonyControllerActionRector\CakePHPControllerActionToSymfonyControllerActionRectorTest
 */
final class CakePHPControllerActionToSymfonyControllerActionRector extends AbstractCakePHPRector
{
    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Migrate CakePHP 2.4 Controller action to Symfony 5', [
            new CodeSample(
                <<<'PHP'
class HomepageController extends \AppController
{
    public function index()
    {
        $value = 5;
    }
}
PHP
,
                <<<'PHP'
use Symfony\Component\HttpFoundation\Response;

class HomepageController extends \AppController
{
    public function index(): Response
    {
        $value = 5;
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
        return [ClassMethod::class];
    }

    /**
     * @param ClassMethod $node
     */
    public function refactor(Node $node): ?Node
    {
        if (! $this->isInCakePHPController($node)) {
            return null;
        }

        if (! $node->isPublic()) {
            return null;
        }

        $node->returnType = new FullyQualified('Symfony\Component\HttpFoundation\Response');

        return $node;
    }
}
