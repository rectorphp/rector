<?php

declare(strict_types=1);

namespace Rector\Compatibility\ValueObject;

use PhpParser\Node\Stmt\Property;
use PHPStan\PhpDocParser\Ast\PhpDoc\ParamTagValueNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\VarTagValueNode;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use Rector\Core\Exception\ShouldNotHappenException;

final class PropertyWithPhpDocInfo
{
    public function __construct(
        private readonly string $propertyName,
        private readonly Property $property,
        private readonly PhpDocInfo $phpDocInfo
    ) {
    }

    public function getProperty(): Property
    {
        return $this->property;
    }

    public function getPhpDocInfo(): PhpDocInfo
    {
        return $this->phpDocInfo;
    }

    public function getPropertyName(): string
    {
        return $this->propertyName;
    }

    public function getParamTagValueNode(): ParamTagValueNode
    {
        $varTagValueNode = $this->phpDocInfo->getVarTagValueNode();

        if (! $varTagValueNode instanceof VarTagValueNode) {
            throw new ShouldNotHappenException();
        }

        return new ParamTagValueNode($varTagValueNode->type, false, '$' . $this->propertyName, '');
    }
}
