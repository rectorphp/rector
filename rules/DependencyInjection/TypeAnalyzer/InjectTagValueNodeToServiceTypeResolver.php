<?php

declare(strict_types=1);

namespace Rector\DependencyInjection\TypeAnalyzer;

use PhpParser\Node\Stmt\Property;
use PHPStan\PhpDocParser\Ast\Node;
use PHPStan\Type\Type;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use Rector\BetterPhpDocParser\ValueObject\PhpDocNode\PHPDI\PHPDIInjectTagValueNode;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\Symfony\PhpDoc\Node\JMS\JMSInjectTagValueNode;

final class InjectTagValueNodeToServiceTypeResolver
{
    /**
     * @var JMSDITypeResolver
     */
    private $jmsdiTypeResolver;

    public function __construct(JMSDITypeResolver $jmsdiTypeResolver)
    {
        $this->jmsdiTypeResolver = $jmsdiTypeResolver;
    }

    public function resolve(Property $property, PhpDocInfo $phpDocInfo, Node $node): Type
    {
        if ($node instanceof JMSInjectTagValueNode) {
            return $this->jmsdiTypeResolver->resolve($property, $node);
        }

        if ($node instanceof PHPDIInjectTagValueNode) {
            return $phpDocInfo->getVarType();
        }

        throw new ShouldNotHappenException();
    }
}
