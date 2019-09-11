<?php declare(strict_types=1);

namespace Rector\BetterPhpDocParser\PhpDocParser;

use DI\Annotation\Inject as PHPDIInjectAlias;
use JMS\DiExtraBundle\Annotation\Inject;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Property;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagValueNode;
use PHPStan\PhpDocParser\Parser\TokenIterator;
use Rector\BetterPhpDocParser\Ast\PhpDoc\JMS\JMSInjectTagValueNode;
use Rector\BetterPhpDocParser\Ast\PhpDoc\PHPDI\PHPDIInjectTagValueNode;
use Rector\PhpParser\Node\Resolver\NameResolver;

final class InjectPhpDocParser extends AbstractPhpDocParser
{
    /**
     * @var NameResolver
     */
    private $nameResolver;

    public function __construct(NameResolver $nameResolver)
    {
        $this->nameResolver = $nameResolver;
    }

    public function parse(TokenIterator $tokenIterator, string $tag): ?PhpDocTagValueNode
    {
        /** @var Class_|Property $currentPhpNode */
        $currentPhpNode = $this->getCurrentPhpNode();

        // needed for proper doc block formatting
        $this->resolveAnnotationContent($tokenIterator);

        // Property tags
        if ($currentPhpNode instanceof Property) {
            if ($tag === '@DI\Inject') {
                /** @var Inject $inject */
                $inject = $this->nodeAnnotationReader->readPropertyAnnotation(
                    $currentPhpNode,
                    JMSInjectTagValueNode::CLASS_NAME
                );

                if ($inject->value === null) {
                    $serviceName = $this->nameResolver->getName($currentPhpNode);
                } else {
                    $serviceName = $inject->value;
                }

                return new JMSInjectTagValueNode($serviceName, $inject->required, $inject->strict);
            }

            if ($tag === '@Inject') {
                /** @var PHPDIInjectAlias $inject */
                $inject = $this->nodeAnnotationReader->readPropertyAnnotation(
                    $currentPhpNode,
                    PHPDIInjectTagValueNode::CLASS_NAME
                );

                return new PHPDIInjectTagValueNode($inject->getName());
            }
        }

        return null;
    }
}
