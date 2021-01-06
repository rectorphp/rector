<?php

declare(strict_types=1);

namespace Rector\BetterPhpDocParser\PhpDocNodeFactory;

use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagValueNode;
use Rector\BetterPhpDocParser\ValueObject\PhpDocNode\Doctrine\Class_\EmbeddableTagValueNode;
use Rector\BetterPhpDocParser\ValueObject\PhpDocNode\Doctrine\Class_\EntityTagValueNode;
use Rector\BetterPhpDocParser\ValueObject\PhpDocNode\Doctrine\Class_\InheritanceTypeTagValueNode;
use Rector\BetterPhpDocParser\ValueObject\PhpDocNode\Doctrine\Property_\ColumnTagValueNode;
use Rector\BetterPhpDocParser\ValueObject\PhpDocNode\Doctrine\Property_\CustomIdGeneratorTagValueNode;
use Rector\BetterPhpDocParser\ValueObject\PhpDocNode\Doctrine\Property_\GeneratedValueTagValueNode;
use Rector\BetterPhpDocParser\ValueObject\PhpDocNode\Doctrine\Property_\IdTagValueNode;
use Rector\BetterPhpDocParser\ValueObject\PhpDocNode\Doctrine\Property_\JoinColumnTagValueNode;
use Rector\BetterPhpDocParser\ValueObject\PhpDocNode\Gedmo\BlameableTagValueNode;
use Rector\BetterPhpDocParser\ValueObject\PhpDocNode\Gedmo\LocaleTagValueNode;
use Rector\BetterPhpDocParser\ValueObject\PhpDocNode\Gedmo\LoggableTagValueNode;
use Rector\BetterPhpDocParser\ValueObject\PhpDocNode\Gedmo\SlugTagValueNode;
use Rector\BetterPhpDocParser\ValueObject\PhpDocNode\Gedmo\SoftDeleteableTagValueNode;
use Rector\BetterPhpDocParser\ValueObject\PhpDocNode\Gedmo\TranslatableTagValueNode;
use Rector\BetterPhpDocParser\ValueObject\PhpDocNode\Gedmo\TreeLeftTagValueNode;
use Rector\BetterPhpDocParser\ValueObject\PhpDocNode\Gedmo\TreeLevelTagValueNode;
use Rector\BetterPhpDocParser\ValueObject\PhpDocNode\Gedmo\TreeParentTagValueNode;
use Rector\BetterPhpDocParser\ValueObject\PhpDocNode\Gedmo\TreeRightTagValueNode;
use Rector\BetterPhpDocParser\ValueObject\PhpDocNode\Gedmo\TreeRootTagValueNode;
use Rector\BetterPhpDocParser\ValueObject\PhpDocNode\Gedmo\TreeTagValueNode;
use Rector\BetterPhpDocParser\ValueObject\PhpDocNode\Gedmo\VersionedTagValueNode;
use Rector\BetterPhpDocParser\ValueObject\PhpDocNode\JMS\JMSInjectParamsTagValueNode;
use Rector\BetterPhpDocParser\ValueObject\PhpDocNode\JMS\JMSServiceValueNode;
use Rector\BetterPhpDocParser\ValueObject\PhpDocNode\JMS\SerializerTypeTagValueNode;
use Rector\BetterPhpDocParser\ValueObject\PhpDocNode\PHPDI\PHPDIInjectTagValueNode;
use Rector\BetterPhpDocParser\ValueObject\PhpDocNode\Sensio\SensioMethodTagValueNode;
use Rector\BetterPhpDocParser\ValueObject\PhpDocNode\Sensio\SensioRouteTagValueNode;
use Rector\BetterPhpDocParser\ValueObject\PhpDocNode\Sensio\SensioTemplateTagValueNode;
use Rector\BetterPhpDocParser\ValueObject\PhpDocNode\Symfony\SymfonyRouteTagValueNode;
use Rector\BetterPhpDocParser\ValueObject\PhpDocNode\Symfony\Validator\Constraints\AssertChoiceTagValueNode;
use Rector\BetterPhpDocParser\ValueObject\PhpDocNode\Symfony\Validator\Constraints\AssertEmailTagValueNode;
use Rector\BetterPhpDocParser\ValueObject\PhpDocNode\Symfony\Validator\Constraints\AssertRangeTagValueNode;
use Rector\BetterPhpDocParser\ValueObject\PhpDocNode\Symfony\Validator\Constraints\AssertTypeTagValueNode;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\PhpdocParserPrinter\Contract\AttributeAwareInterface;
use Rector\PhpdocParserPrinter\Contract\PhpDocNodeFactoryInterface;
use Rector\PhpdocParserPrinter\ValueObject\SmartTokenIterator;

final class MultiPhpDocNodeFactory extends AbstractPhpDocNodeFactory implements PhpDocNodeFactoryInterface
{
    /**
     * @return array<string, string>
     */
    private const ANNOTATION_TO_NODE = [
        // doctrine - intentionally in string, so prefixer of rector.phar doesn't prefix it
        'Doctrine\ORM\Mapping\Embeddable' => EmbeddableTagValueNode::class,
        'Doctrine\ORM\Mapping\Entity' => EntityTagValueNode::class,
        'Doctrine\ORM\Mapping\InheritanceType' => InheritanceTypeTagValueNode::class,
        'Doctrine\ORM\Mapping\Column' => ColumnTagValueNode::class,
        'Doctrine\ORM\Mapping\CustomIdGenerator' => CustomIdGeneratorTagValueNode::class,
        'Doctrine\ORM\Mapping\Id' => IdTagValueNode::class,
        'Doctrine\ORM\Mapping\GeneratedValue' => GeneratedValueTagValueNode::class,
        'Doctrine\ORM\Mapping\JoinColumn' => JoinColumnTagValueNode::class,
        // symfony/http-kernel
        'Symfony\Component\Routing\Annotation\Route' => SymfonyRouteTagValueNode::class,
        // symfony/validator
        'Symfony\Component\Validator\Constraints\Range' => AssertRangeTagValueNode::class,
        'Symfony\Component\Validator\Constraints\Type' => AssertTypeTagValueNode::class,
        'Symfony\Component\Validator\Constraints\Choice' => AssertChoiceTagValueNode::class,
        'Symfony\Component\Validator\Constraints\Email' => AssertEmailTagValueNode::class,
        // gedmo
        'Gedmo\Mapping\Annotation\Locale' => LocaleTagValueNode::class,
        'Gedmo\Mapping\Annotation\Blameable' => BlameableTagValueNode::class,
        'Gedmo\Mapping\Annotation\Slug' => SlugTagValueNode::class,
        'Gedmo\Mapping\Annotation\SoftDeleteable' => SoftDeleteableTagValueNode::class,
        'Gedmo\Mapping\Annotation\TreeRoot' => TreeRootTagValueNode::class,
        'Gedmo\Mapping\Annotation\TreeLeft' => TreeLeftTagValueNode::class,
        'Gedmo\Mapping\Annotation\TreeLevel' => TreeLevelTagValueNode::class,
        'Gedmo\Mapping\Annotation\TreeParent' => TreeParentTagValueNode::class,
        'Gedmo\Mapping\Annotation\TreeRight' => TreeRightTagValueNode::class,
        'Gedmo\Mapping\Annotation\Versioned' => VersionedTagValueNode::class,
        'Gedmo\Mapping\Annotation\Translatable' => TranslatableTagValueNode::class,
        'Gedmo\Mapping\Annotation\Loggable' => LoggableTagValueNode::class,
        'Gedmo\Mapping\Annotation\Tree' => TreeTagValueNode::class,
        // Sensio
        'Sensio\Bundle\FrameworkExtraBundle\Configuration\Template' => SensioTemplateTagValueNode::class,
        'Sensio\Bundle\FrameworkExtraBundle\Configuration\Method' => SensioMethodTagValueNode::class,
        'Sensio\Bundle\FrameworkExtraBundle\Configuration\Route' => SensioRouteTagValueNode::class,
        // JMS
        'JMS\DiExtraBundle\Annotation\InjectParams' => JMSInjectParamsTagValueNode::class,
        'JMS\DiExtraBundle\Annotation\Service' => JMSServiceValueNode::class,
        'JMS\Serializer\Annotation\Type' => SerializerTypeTagValueNode::class,
        'DI\Annotation\Inject' => PHPDIInjectTagValueNode::class,
    ];

    public function isMatch(string $tag): bool
    {
        return isset(self::ANNOTATION_TO_NODE[$tag]);
    }

    /**
     * @return (PhpDocTagValueNode&AttributeAwareInterface)|null
     */
    public function create(SmartTokenIterator $smartTokenIterator, string $resolvedTag): ?AttributeAwareInterface
    {
        $currentNode = $this->currentNodeProvider->getNode();
        if ($currentNode === null) {
            throw new ShouldNotHappenException();
        }

        $tagValueNodeClass = self::ANNOTATION_TO_NODE[$resolvedTag];

        $annotation = $this->nodeAnnotationReader->readAnnotation($currentNode, $resolvedTag);
        if ($annotation === null) {
            return null;
        }

        $items = $this->annotationItemsResolver->resolve($annotation);

        return new $tagValueNodeClass($items);
    }
}
