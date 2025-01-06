<?php

declare (strict_types=1);
namespace Rector\TypeDeclaration\NodeAnalyzer;

use PhpParser\Node\Param;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Property;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory;
use Rector\Php80\NodeAnalyzer\PhpAttributeAnalyzer;
final class AutowiredClassMethodOrPropertyAnalyzer
{
    /**
     * @readonly
     */
    private PhpDocInfoFactory $phpDocInfoFactory;
    /**
     * @readonly
     */
    private PhpAttributeAnalyzer $phpAttributeAnalyzer;
    public function __construct(PhpDocInfoFactory $phpDocInfoFactory, PhpAttributeAnalyzer $phpAttributeAnalyzer)
    {
        $this->phpDocInfoFactory = $phpDocInfoFactory;
        $this->phpAttributeAnalyzer = $phpAttributeAnalyzer;
    }
    public function matchAutowiredMethodInClass(Class_ $class) : ?ClassMethod
    {
        foreach ($class->getMethods() as $classMethod) {
            if (!$classMethod->isPublic()) {
                continue;
            }
            if ($classMethod->isMagic()) {
                continue;
            }
            if (!$this->detect($classMethod)) {
                continue;
            }
            return $classMethod;
        }
        return null;
    }
    /**
     * @param \PhpParser\Node\Stmt\ClassMethod|\PhpParser\Node\Param|\PhpParser\Node\Stmt\Property $node
     */
    public function detect($node) : bool
    {
        $nodePhpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($node);
        if ($nodePhpDocInfo->hasByNames(['required', 'inject'])) {
            return \true;
        }
        return $this->phpAttributeAnalyzer->hasPhpAttributes($node, ['Symfony\\Contracts\\Service\\Attribute\\Required', 'Nette\\DI\\Attributes\\Inject']);
    }
}
