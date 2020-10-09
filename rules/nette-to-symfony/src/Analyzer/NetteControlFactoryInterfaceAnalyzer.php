<?php

declare(strict_types=1);

namespace Rector\NetteToSymfony\Analyzer;

use PhpParser\Node\Stmt\Interface_;
use PHPStan\Type\TypeWithClassName;
use Rector\PHPStan\Type\ShortenedObjectType;
use Rector\TypeDeclaration\TypeInferer\ReturnTypeInferer;

final class NetteControlFactoryInterfaceAnalyzer
{
    /**
     * @var ReturnTypeInferer
     */
    private $returnTypeInferer;

    public function __construct(ReturnTypeInferer $returnTypeInferer)
    {
        $this->returnTypeInferer = $returnTypeInferer;
    }

    /**
     * @see https://doc.nette.org/en/3.0/components#toc-components-with-dependencies
     */
    public function isComponentFactoryInterface(Interface_ $interface): bool
    {
        foreach ($interface->getMethods() as $classMethod) {
            $returnType = $this->returnTypeInferer->inferFunctionLike($classMethod);
            if (! $returnType instanceof TypeWithClassName) {
                return false;
            }

            $className = $this->resolveClassName($returnType);
            if (is_a($className, 'Nette\Application\UI\Control', true)) {
                return true;
            }

            if (is_a($className, 'Nette\Application\UI\Form', true)) {
                return true;
            }
        }

        return false;
    }

    private function resolveClassName(TypeWithClassName $typeWithClassName): string
    {
        if ($typeWithClassName instanceof ShortenedObjectType) {
            return $typeWithClassName->getFullyQualifiedName();
        }

        return $typeWithClassName->getClassName();
    }
}
