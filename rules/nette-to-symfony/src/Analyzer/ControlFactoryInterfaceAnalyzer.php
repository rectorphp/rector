<?php

declare(strict_types=1);

namespace Rector\NetteToSymfony\Analyzer;

use PhpParser\Node\Stmt\Interface_;
use PHPStan\Type\TypeWithClassName;
use Rector\TypeDeclaration\TypeInferer\ReturnTypeInferer;

final class ControlFactoryInterfaceAnalyzer
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

            $className = $returnType->getClassName();

            if (is_a($className, 'Nette\Application\UI\Control', true)) {
                return true;
            }

            if (is_a($className, 'Nette\Application\UI\Form', true)) {
                return true;
            }
        }

        return false;
    }
}
