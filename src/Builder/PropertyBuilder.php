<?php declare(strict_types=1);

namespace Rector\Builder;

use Nette\Utils\Arrays;
use PhpParser\BuilderFactory;
use PhpParser\Comment\Doc;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Property;

final class PropertyBuilder
{
    /**
     * @var BuilderFactory
     */
    private $builderFactory;

    public function __construct(BuilderFactory $builderFactory)
    {
        $this->builderFactory = $builderFactory;
    }

    public function addPropertyToClass(Class_ $classNode, string $propertyType, string $propertyName): void
    {
        $propertyNode = $this->buildPrivatePropertyNode($propertyType, $propertyName);

        // add before first method
        foreach ($classNode->stmts as $key => $classElementNode) {
            if ($classElementNode instanceof ClassMethod) {
                Arrays::insertBefore(
                    $classNode->stmts,
                    $key,
                    ['before_' . $key => $propertyNode]
                );

                return;
            }
        }

        // or after last property
        $previousElement = null;
        foreach ($classNode->stmts as $key => $classElementNode) {
            if ($previousElement instanceof Property && ! $classElementNode instanceof Property) {
                Arrays::insertBefore(
                    $classNode->stmts,
                    $key,
                    ['before_' . $key => $propertyNode]
                );

                return;
            }

            $previousElement = $classElementNode;
        }

        $classNode->stmts[] = $propertyNode;
    }

    private function buildPrivatePropertyNode(string $propertyType, string $propertyName): Property
    {
        $docComment = $this->createDocWithVarAnnotation($propertyType);

        $propertyBuilder = $this->builderFactory->property($propertyName)
            ->makePrivate()
            ->setDocComment($docComment);

        return $propertyBuilder->getNode();
    }

    private function createDocWithVarAnnotation(string $propertyType): Doc
    {
        return new Doc('/**'
            . PHP_EOL . ' * @var ' . $propertyType
            . PHP_EOL . ' */');
    }
}
