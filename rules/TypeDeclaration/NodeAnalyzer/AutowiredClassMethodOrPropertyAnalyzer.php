<?php

declare (strict_types=1);
namespace Rector\TypeDeclaration\NodeAnalyzer;

use PhpParser\Node\Param;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Property;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory;
use Rector\Php80\NodeAnalyzer\PhpAttributeAnalyzer;
use RectorPrefix20220501\Symfony\Contracts\Service\Attribute\Required;
final class AutowiredClassMethodOrPropertyAnalyzer
{
    /**
     * @readonly
     * @var \Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory
     */
    private $phpDocInfoFactory;
    /**
     * @readonly
     * @var \Rector\Php80\NodeAnalyzer\PhpAttributeAnalyzer
     */
    private $phpAttributeAnalyzer;
    public function __construct(\Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory $phpDocInfoFactory, \Rector\Php80\NodeAnalyzer\PhpAttributeAnalyzer $phpAttributeAnalyzer)
    {
        $this->phpDocInfoFactory = $phpDocInfoFactory;
        $this->phpAttributeAnalyzer = $phpAttributeAnalyzer;
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
        return $this->phpAttributeAnalyzer->hasPhpAttributes($node, [\RectorPrefix20220501\Symfony\Contracts\Service\Attribute\Required::class, 'Nette\\DI\\Attributes\\Inject']);
    }
}
