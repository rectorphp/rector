<?php

declare(strict_types=1);

namespace Rector\CakePHPToSymfony\Rector\Class_;

use PhpParser\Node;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Stmt\Class_;
use Rector\CakePHPToSymfony\Rector\AbstractCakePHPRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;

/**
 * @see https://book.cakephp.org/2/en/tutorials-and-examples/blog/part-two.html
 * @see https://symfony.com/doc/5.0/controller.html
 * @see https://symfony.com/doc/5.0/controller.html#rendering-templates
 *
 * @see \Rector\CakePHPToSymfony\Tests\Rector\Class_\CakePHPControllerToSymfonyControllerRector\CakePHPControllerToSymfonyControllerRectorTest
 */
final class CakePHPControllerToSymfonyControllerRector extends AbstractCakePHPRector
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
        return [Class_::class];
    }

    /**
     * @param Class_ $node
     */
    public function refactor(Node $node): ?Node
    {
        if (! $this->isInCakePHPController($node)) {
            return null;
        }

        $node->extends = new FullyQualified('Symfony\Bundle\FrameworkBundle\Controller\AbstractController');

        return $node;
    }
}
