<?php

declare (strict_types=1);
namespace Rector\TypeDeclaration\NodeAnalyzer;

use PhpParser\Node\Stmt\ClassMethod;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory;
use Rector\NodeNameResolver\NodeNameResolver;
use RectorPrefix20210710\Symfony\Contracts\Service\Attribute\Required;
final class AutowiredClassMethodAnalyzer
{
    /**
     * @var \Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory
     */
    private $phpDocInfoFactory;
    /**
     * @var \Rector\NodeNameResolver\NodeNameResolver
     */
    private $nodeNameResolver;
    public function __construct(\Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory $phpDocInfoFactory, \Rector\NodeNameResolver\NodeNameResolver $nodeNameResolver)
    {
        $this->phpDocInfoFactory = $phpDocInfoFactory;
        $this->nodeNameResolver = $nodeNameResolver;
    }
    public function detect(\PhpParser\Node\Stmt\ClassMethod $classMethod) : bool
    {
        $classMethodPhpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($classMethod);
        if ($classMethodPhpDocInfo->hasByNames(['required', 'inject'])) {
            return \true;
        }
        foreach ($classMethod->attrGroups as $attrGroup) {
            foreach ($attrGroup->attrs as $attribute) {
                if ($this->nodeNameResolver->isNames($attribute->name, [\RectorPrefix20210710\Symfony\Contracts\Service\Attribute\Required::class, 'Nette\\DI\\Attributes\\Inject'])) {
                    return \true;
                }
            }
        }
        return \false;
    }
}
