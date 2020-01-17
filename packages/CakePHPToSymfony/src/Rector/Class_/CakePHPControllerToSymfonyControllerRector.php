<?php

declare(strict_types=1);

namespace Rector\CakePHPToSymfony\Rector\Class_;

use PhpParser\Node;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;

/**
 * @see https://book.cakephp.org/2/en/tutorials-and-examples/blog/part-two.html
 * @see https://symfony.com/doc/5.0/controller.html
 * @see https://symfony.com/doc/5.0/controller.html#rendering-templates
 *
 * @see \Rector\CakePHPToSymfony\Tests\Rector\Class_\CakePHPControllerToSymfonyControllerRector\CakePHPControllerToSymfonyControllerRectorTest
 */
final class CakePHPControllerToSymfonyControllerRector extends AbstractRector
{
    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Migrate CakePHP 2.4 Controller to Symfony 5', [
            new CodeSample(
                <<<'PHP'
class HomepageController extends AppController
{
    public function index()
    {
        $value = 5;
        $this->set('name', $value);
    }
}
PHP
,
                <<<'PHP'
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class HomepageController extends AbstractController
{
    public function index(): Response
    {
        $value = 5;
        return $this->renderResponse('homepage/index.ctp', [
            'name' => $value
        ]);
    }
}
PHP

            )
        ]);
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [\PhpParser\Node\Stmt\Class_::class];
    }

    /**
     * @param \PhpParser\Node\Stmt\Class_ $node
     */
    public function refactor(Node $node): ?Node
    {
        // change the node

        return $node;
    }
}
