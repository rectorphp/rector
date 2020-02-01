<?php

declare(strict_types=1);

namespace Rector\CakePHPToSymfony\Rector\Class_;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use Rector\BetterPhpDocParser\PhpDocNode\Symfony\SymfonyRouteTagValueNode;
use Rector\CakePHPToSymfony\Rector\AbstractCakePHPRector;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\PHPStan\Type\FullyQualifiedObjectType;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;
use Rector\Util\RectorStrings;

/**
 * @inspiration
 * @see \Rector\NetteToSymfony\Rector\ClassMethod\RouterListToControllerAnnotationsRector
 *
 * @see \Rector\CakePHPToSymfony\Tests\Rector\Class_\CakePHPImplicitRouteToExplicitRouteAnnotationRector\CakePHPImplicitRouteToExplicitRouteAnnotationRectorTest
 */
final class CakePHPImplicitRouteToExplicitRouteAnnotationRector extends AbstractCakePHPRector
{
    /**
     * @var string
     */
    private const HAS_FRESH_ROUTE_ANNOTATION_ATTRIBUTE = 'has_fresh_route_annotation';

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

            /** @var string $className */
            $className = $node->getAttribute(AttributeKey::CLASS_NAME);
            $shortClassName = $this->getShortName($className);

            $methodName = $this->getName($classMethod);

            $combined = RectorStrings::removeSuffixes($shortClassName, ['Controller']) . '/' . $methodName;
            $path = '/' . RectorStrings::camelCaseToSlashes($combined);
            $name = RectorStrings::camelCaseToUnderscore($combined);

            $symfonyRoutePhpDocTagValueNode = $this->createSymfonyRoutePhpDocTagValueNode($path, $name);
            $this->addSymfonyRouteShortTagNodeWithUse($symfonyRoutePhpDocTagValueNode, $classMethod);
        }

        return $node;
    }

    private function createSymfonyRoutePhpDocTagValueNode(string $path, string $name): SymfonyRouteTagValueNode
    {
        return new SymfonyRouteTagValueNode($path, $name);
    }

    /**
     * @todo reuse from RouterListToControllerAnnotationsRector
     */
    private function addSymfonyRouteShortTagNodeWithUse(
        SymfonyRouteTagValueNode $symfonyRouteTagValueNode,
        ClassMethod $classMethod
    ): void {
        // @todo use empty phpdoc info
        $this->docBlockManipulator->addTagValueNodeWithShortName($classMethod, $symfonyRouteTagValueNode);

        $symfonyRouteUseObjectType = new FullyQualifiedObjectType(SymfonyRouteTagValueNode::CLASS_NAME);
        $this->addUseType($symfonyRouteUseObjectType, $classMethod);

        $classMethod->setAttribute(self::HAS_FRESH_ROUTE_ANNOTATION_ATTRIBUTE, true);
    }
}
