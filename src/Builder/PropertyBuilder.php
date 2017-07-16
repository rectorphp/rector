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
        // 9. add a property
        $propertyBuilder = $this->builderFactory->property($propertyName)
            ->makePrivate()
            ->setDocComment(new Doc('/**' . PHP_EOL . ' * @var ' . $propertyType . PHP_EOL . ' */'));

        $this->addProperty($classNode, $propertyBuilder->getNode());
    }

    private function addProperty(Class_ $classNode, Property $propertyNode): void
    {
        foreach ($classNode->stmts as $key => $classElementNode) {
            if ($classElementNode instanceof Property || $classElementNode instanceof ClassMethod) {
                Arrays::insertBefore(
                    $classNode->stmts,
                    $key,
                    ['before_' . $key => $propertyNode]
                );

                return;
            }
        }

        $classNode->stmts[] = $propertyNode;
    }
}
