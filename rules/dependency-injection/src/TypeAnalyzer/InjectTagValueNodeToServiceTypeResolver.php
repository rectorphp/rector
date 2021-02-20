<?php

declare(strict_types=1);

namespace Rector\DependencyInjection\TypeAnalyzer;

use PhpParser\Node\Stmt\Property;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagValueNode;
use PHPStan\Type\Type;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use Rector\BetterPhpDocParser\ValueObject\PhpDocNode\JMS\JMSInjectTagValueNode;
use Rector\BetterPhpDocParser\ValueObject\PhpDocNode\PHPDI\PHPDIInjectTagValueNode;
use Rector\Core\Exception\ShouldNotHappenException;

final class InjectTagValueNodeToServiceTypeResolver
{
    /**
     * @var JMSDITypeResolver
     */
    private $jmsDITypeResolver;

    public function __construct(JMSDITypeResolver $jmsDITypeResolver)
    {
        $this->jmsDITypeResolver = $jmsDITypeResolver;
    }

    public function resolve(Property $property, PhpDocInfo $phpDocInfo, PhpDocTagValueNode $phpDocTagValueNode): Type
    {
        if ($phpDocTagValueNode instanceof JMSInjectTagValueNode) {
            return $this->jmsDITypeResolver->resolve($property, $phpDocTagValueNode);
        }

        if ($phpDocTagValueNode instanceof PHPDIInjectTagValueNode) {
            return $phpDocInfo->getVarType();
        }

        throw new ShouldNotHappenException();
    }
}
