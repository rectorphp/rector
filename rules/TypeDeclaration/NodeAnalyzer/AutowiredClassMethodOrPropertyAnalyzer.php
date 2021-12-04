<?php

declare(strict_types=1);

namespace Rector\TypeDeclaration\NodeAnalyzer;

use PhpParser\Node\Param;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Property;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory;
use Rector\Php80\NodeAnalyzer\PhpAttributeAnalyzer;
use Symfony\Contracts\Service\Attribute\Required;

final class AutowiredClassMethodOrPropertyAnalyzer
{
    public function __construct(
        private readonly PhpDocInfoFactory $phpDocInfoFactory,
        private readonly PhpAttributeAnalyzer $phpAttributeAnalyzer
    ) {
    }

    public function detect(ClassMethod | Param | Property $node): bool
    {
        $nodePhpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($node);
        if ($nodePhpDocInfo->hasByNames(['required', 'inject'])) {
            return true;
        }

        return $this->phpAttributeAnalyzer->hasPhpAttributes($node, [Required::class, 'Nette\DI\Attributes\Inject']);
    }
}
