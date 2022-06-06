<?php

declare (strict_types=1);
namespace Rector\Compatibility\ValueObject;

use PhpParser\Node\Stmt\Property;
use PHPStan\PhpDocParser\Ast\PhpDoc\ParamTagValueNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\VarTagValueNode;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use Rector\Core\Exception\ShouldNotHappenException;
final class PropertyWithPhpDocInfo
{
    /**
     * @readonly
     * @var string
     */
    private $propertyName;
    /**
     * @readonly
     * @var \PhpParser\Node\Stmt\Property
     */
    private $property;
    /**
     * @readonly
     * @var \Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo
     */
    private $phpDocInfo;
    public function __construct(string $propertyName, \PhpParser\Node\Stmt\Property $property, \Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo $phpDocInfo)
    {
        $this->propertyName = $propertyName;
        $this->property = $property;
        $this->phpDocInfo = $phpDocInfo;
    }
    public function getProperty() : \PhpParser\Node\Stmt\Property
    {
        return $this->property;
    }
    public function getPhpDocInfo() : \Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo
    {
        return $this->phpDocInfo;
    }
    public function getPropertyName() : string
    {
        return $this->propertyName;
    }
    public function getParamTagValueNode() : \PHPStan\PhpDocParser\Ast\PhpDoc\ParamTagValueNode
    {
        $varTagValueNode = $this->phpDocInfo->getVarTagValueNode();
        if (!$varTagValueNode instanceof \PHPStan\PhpDocParser\Ast\PhpDoc\VarTagValueNode) {
            throw new \Rector\Core\Exception\ShouldNotHappenException();
        }
        return new \PHPStan\PhpDocParser\Ast\PhpDoc\ParamTagValueNode($varTagValueNode->type, \false, '$' . $this->propertyName, '');
    }
}
