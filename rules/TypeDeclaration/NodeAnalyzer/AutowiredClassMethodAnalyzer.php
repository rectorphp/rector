<?php

declare(strict_types=1);

namespace Rector\TypeDeclaration\NodeAnalyzer;

use PhpParser\Node\Stmt\ClassMethod;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory;
use Rector\NodeNameResolver\NodeNameResolver;
use Symfony\Contracts\Service\Attribute\Required;

final class AutowiredClassMethodAnalyzer
{
    public function __construct(
        private PhpDocInfoFactory $phpDocInfoFactory,
        private NodeNameResolver $nodeNameResolver
    ) {
    }

    public function detect(ClassMethod $classMethod): bool
    {
        $classMethodPhpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($classMethod);
        if ($classMethodPhpDocInfo->hasByNames(['required', 'inject'])) {
            return true;
        }

        foreach ($classMethod->attrGroups as $attrGroup) {
            foreach ($attrGroup->getAttributes() as $attribute) {
                if ($this->nodeNameResolver->isNames(
                    $attribute->name,
                    [Required::class, 'Nette\DI\Attributes\Inject']
                )) {
                    return true;
                }
            }
        }

        return false;
    }
}
