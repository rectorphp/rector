<?php

declare(strict_types=1);

namespace Rector\TypeDeclaration\NodeAnalyzer;

use PhpParser\Node\Param;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Property;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory;
use Rector\NodeNameResolver\NodeNameResolver;
use Symfony\Contracts\Service\Attribute\Required;

final class AutowiredClassMethodOrPropertyAnalyzer
{
    public function __construct(
        private PhpDocInfoFactory $phpDocInfoFactory,
        private NodeNameResolver $nodeNameResolver
    ) {
    }

    public function detect(ClassMethod | Param | Property $node): bool
    {
        $nodePhpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($node);
        if ($nodePhpDocInfo->hasByNames(['required', 'inject'])) {
            return true;
        }

        foreach ($node->attrGroups as $attrGroup) {
            foreach ($attrGroup->attrs as $attribute) {
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
