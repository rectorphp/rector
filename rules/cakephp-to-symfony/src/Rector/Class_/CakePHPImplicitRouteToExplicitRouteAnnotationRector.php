<?php

declare(strict_types=1);

namespace Rector\CakePHPToSymfony\Rector\Class_;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use Rector\BetterPhpDocParser\PhpDocNode\Symfony\SymfonyRouteTagValueNode;
use Rector\CakePHPToSymfony\Rector\AbstractCakePHPRector;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;
use Rector\Core\Util\StaticRectorStrings;
use Rector\FrameworkMigration\Symfony\ImplicitToExplicitRoutingAnnotationDecorator;
use Rector\NodeTypeResolver\Node\AttributeKey;

/**
 * @inspiration
 * @see \Rector\NetteToSymfony\Rector\ClassMethod\RouterListToControllerAnnotationsRector
 *
 * @see \Rector\CakePHPToSymfony\Tests\Rector\Class_\CakePHPImplicitRouteToExplicitRouteAnnotationRector\CakePHPImplicitRouteToExplicitRouteAnnotationRectorTest
 */
final class CakePHPImplicitRouteToExplicitRouteAnnotationRector extends AbstractCakePHPRector
{
    /**
     * @var ImplicitToExplicitRoutingAnnotationDecorator
     */
    private $implicitToExplicitRoutingAnnotationDecorator;

    public function __construct(
        ImplicitToExplicitRoutingAnnotationDecorator $implicitToExplicitRoutingAnnotationDecorator
    ) {
        $this->implicitToExplicitRoutingAnnotationDecorator = $implicitToExplicitRoutingAnnotationDecorator;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Migrate CakePHP implicit routes to Symfony @route annotations', [
            new CodeSample(
                <<<'PHP'
class PaymentsController extends AppController
{
    public function index()
    {
    }
}
PHP
,
                <<<'PHP'
use Symfony\Component\Routing\Annotation\Route;

class AdminPaymentsController extends AppController
{
    /**
     * @Route(path="/payments/index", name="payments_index")
     */
    public function index()
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

        foreach ($node->getMethods() as $classMethod) {
            if (! $classMethod->isPublic()) {
                continue;
            }

            /** @var string $shortClassName */
            $shortClassName = $node->getAttribute(AttributeKey::CLASS_SHORT_NAME);

            $methodName = $this->getName($classMethod);

            $combined = StaticRectorStrings::removeSuffixes($shortClassName, ['Controller']) . '/' . $methodName;
            $path = '/' . StaticRectorStrings::camelCaseToSlashes($combined);
            $name = StaticRectorStrings::camelCaseToUnderscore($combined);

            $symfonyRoutePhpDocTagValueNode = $this->createSymfonyRoutePhpDocTagValueNode($path, $name);

            $this->implicitToExplicitRoutingAnnotationDecorator->decorateClassMethodWithRouteAnnotation(
                $classMethod,
                $symfonyRoutePhpDocTagValueNode
            );
        }

        return $node;
    }

    private function createSymfonyRoutePhpDocTagValueNode(string $path, string $name): SymfonyRouteTagValueNode
    {
        return new SymfonyRouteTagValueNode([
            'path' => $path,
            'name' => $name,
        ]);
    }
}
