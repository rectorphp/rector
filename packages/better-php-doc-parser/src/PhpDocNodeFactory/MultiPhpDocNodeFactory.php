<?php

declare(strict_types=1);

namespace Rector\BetterPhpDocParser\PhpDocNodeFactory;

use Doctrine\ORM\Mapping\Annotation;
use Doctrine\ORM\Mapping\Embedded;
use Doctrine\ORM\Mapping\ManyToMany;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\OneToMany;
use Doctrine\ORM\Mapping\OneToOne;
use PhpParser\Node;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagValueNode;
use PHPStan\PhpDocParser\Parser\TokenIterator;
use Rector\BetterPhpDocParser\Contract\Doctrine\DoctrineRelationTagValueNodeInterface;
use Rector\BetterPhpDocParser\Contract\PhpDocNodeFactoryInterface;
use Rector\BetterPhpDocParser\Printer\ArrayPartPhpDocTagPrinter;
use Rector\BetterPhpDocParser\Printer\TagValueNodePrinter;
use Rector\BetterPhpDocParser\ValueObject\PhpDocNode\AbstractTagValueNode;
use Rector\BetterPhpDocParser\ValueObject\PhpDocNode\Doctrine\Class_\EmbeddableTagValueNode;
use Rector\BetterPhpDocParser\ValueObject\PhpDocNode\Doctrine\Class_\EmbeddedTagValueNode;
use Rector\BetterPhpDocParser\ValueObject\PhpDocNode\Doctrine\Class_\EntityTagValueNode;
use Rector\BetterPhpDocParser\ValueObject\PhpDocNode\Doctrine\Class_\InheritanceTypeTagValueNode;
use Rector\BetterPhpDocParser\ValueObject\PhpDocNode\Doctrine\Property_\ColumnTagValueNode;
use Rector\BetterPhpDocParser\ValueObject\PhpDocNode\Doctrine\Property_\CustomIdGeneratorTagValueNode;
use Rector\BetterPhpDocParser\ValueObject\PhpDocNode\Doctrine\Property_\GeneratedValueTagValueNode;
use Rector\BetterPhpDocParser\ValueObject\PhpDocNode\Doctrine\Property_\IdTagValueNode;
use Rector\BetterPhpDocParser\ValueObject\PhpDocNode\Doctrine\Property_\JoinColumnTagValueNode;
use Rector\BetterPhpDocParser\ValueObject\PhpDocNode\Doctrine\Property_\ManyToManyTagValueNode;
use Rector\BetterPhpDocParser\ValueObject\PhpDocNode\Doctrine\Property_\ManyToOneTagValueNode;
use Rector\BetterPhpDocParser\ValueObject\PhpDocNode\Doctrine\Property_\OneToManyTagValueNode;
use Rector\BetterPhpDocParser\ValueObject\PhpDocNode\Doctrine\Property_\OneToOneTagValueNode;
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

final class MultiPhpDocNodeFactory extends AbstractPhpDocNodeFactory implements PhpDocNodeFactoryInterface
{
    /**
     * @var ArrayPartPhpDocTagPrinter
     */
    private $arrayPartPhpDocTagPrinter;

    /**
     * @var TagValueNodePrinter
     */
    private $tagValueNodePrinter;

    public function __construct(
        ArrayPartPhpDocTagPrinter $arrayPartPhpDocTagPrinter,
        TagValueNodePrinter $tagValueNodePrinter
    ) {
        $this->arrayPartPhpDocTagPrinter = $arrayPartPhpDocTagPrinter;
        $this->tagValueNodePrinter = $tagValueNodePrinter;
    }

    /**
     * @return array<class-string<AbstractTagValueNode>, class-string<Annotation>>
     */
    public function getTagValueNodeClassesToAnnotationClasses(): array
    {
        return [
            // tag value node class => annotation class

            // doctrine - intentionally in string, so prefixer of rector.phar doesn't prefix it
            EmbeddableTagValueNode::class => 'Doctrine\ORM\Mapping\Embeddable',
            EntityTagValueNode::class => 'Doctrine\ORM\Mapping\Entity',
            InheritanceTypeTagValueNode::class => 'Doctrine\ORM\Mapping\InheritanceType',
            ColumnTagValueNode::class => 'Doctrine\ORM\Mapping\Column',
            CustomIdGeneratorTagValueNode::class => 'Doctrine\ORM\Mapping\CustomIdGenerator',
            IdTagValueNode::class => 'Doctrine\ORM\Mapping\Id',
            GeneratedValueTagValueNode::class => 'Doctrine\ORM\Mapping\GeneratedValue',
            JoinColumnTagValueNode::class => 'Doctrine\ORM\Mapping\JoinColumn',
            // symfony/http-kernel
            SymfonyRouteTagValueNode::class => 'Symfony\Component\Routing\Annotation\Route',
            // symfony/validator
            AssertRangeTagValueNode::class => 'Symfony\Component\Validator\Constraints\Range',
            AssertTypeTagValueNode::class => 'Symfony\Component\Validator\Constraints\Type',
            AssertChoiceTagValueNode::class => 'Symfony\Component\Validator\Constraints\Choice',
            AssertEmailTagValueNode::class => 'Symfony\Component\Validator\Constraints\Email',
            // gedmo
            LocaleTagValueNode::class => 'Gedmo\Mapping\Annotation\Locale',
            BlameableTagValueNode::class => 'Gedmo\Mapping\Annotation\Blameable',
            SlugTagValueNode::class => 'Gedmo\Mapping\Annotation\Slug',
            SoftDeleteableTagValueNode::class => 'Gedmo\Mapping\Annotation\SoftDeleteable',
            TreeRootTagValueNode::class => 'Gedmo\Mapping\Annotation\TreeRoot',
            TreeLeftTagValueNode::class => 'Gedmo\Mapping\Annotation\TreeLeft',
            TreeLevelTagValueNode::class => 'Gedmo\Mapping\Annotation\TreeLevel',
            TreeParentTagValueNode::class => 'Gedmo\Mapping\Annotation\TreeParent',
            TreeRightTagValueNode::class => 'Gedmo\Mapping\Annotation\TreeRight',
            VersionedTagValueNode::class => 'Gedmo\Mapping\Annotation\Versioned',
            TranslatableTagValueNode::class => 'Gedmo\Mapping\Annotation\Translatable',
            LoggableTagValueNode::class => 'Gedmo\Mapping\Annotation\Loggable',
            TreeTagValueNode::class => 'Gedmo\Mapping\Annotation\Tree',
            // Sensio
            SensioTemplateTagValueNode::class => 'Sensio\Bundle\FrameworkExtraBundle\Configuration\Template',
            SensioMethodTagValueNode::class => 'Sensio\Bundle\FrameworkExtraBundle\Configuration\Method',
            SensioRouteTagValueNode::class => 'Sensio\Bundle\FrameworkExtraBundle\Configuration\Route',
            // JMS
            JMSInjectParamsTagValueNode::class => 'JMS\DiExtraBundle\Annotation\InjectParams',
            JMSServiceValueNode::class => 'JMS\DiExtraBundle\Annotation\Service',
            SerializerTypeTagValueNode::class => 'JMS\Serializer\Annotation\Type',
            PHPDIInjectTagValueNode::class => 'DI\Annotation\Inject',

            // Doctrine
            OneToOneTagValueNode::class => 'Doctrine\ORM\Mapping\OneToOne',
            OneToManyTagValueNode::class => 'Doctrine\ORM\Mapping\OneToMany',
            ManyToManyTagValueNode::class => 'Doctrine\ORM\Mapping\ManyToMany',
            ManyToOneTagValueNode::class => 'Doctrine\ORM\Mapping\ManyToOne',

            // @todo cover with reflection / services to avoid forgetting registering it?
            EmbeddedTagValueNode::class => 'Doctrine\ORM\Mapping\Embedded',
        ];
    }

    public function createFromNodeAndTokens(
        Node $node,
        TokenIterator $tokenIterator,
        string $annotationClass
    ): ?PhpDocTagValueNode {
        $annotation = $this->nodeAnnotationReader->readAnnotation($node, $annotationClass);
        if ($annotation === null) {
            return null;
        }

        $tagValueNodeClassesToAnnotationClasses = $this->getTagValueNodeClassesToAnnotationClasses();
        $tagValueNodeClass = array_search($annotationClass, $tagValueNodeClassesToAnnotationClasses, true);
        if ($tagValueNodeClass === false) {
            return null;
        }

        $items = $this->annotationItemsResolver->resolve($annotation);
        $content = $this->annotationContentResolver->resolveFromTokenIterator($tokenIterator);

        if (is_a($tagValueNodeClass, DoctrineRelationTagValueNodeInterface::class, true)) {
            /** @var ManyToOne|OneToMany|ManyToMany|OneToOne|Embedded $annotation */
            $fullyQualifiedTargetEntity = $this->resolveEntityClass($annotation, $node);
            return new $tagValueNodeClass(
                $this->arrayPartPhpDocTagPrinter,
                $this->tagValueNodePrinter,
                $items,
                $content,
                $fullyQualifiedTargetEntity
            );
        }

        return new $tagValueNodeClass(
            $this->arrayPartPhpDocTagPrinter,
            $this->tagValueNodePrinter,
            $items,
            $content
        );
    }

    /**
     * @param ManyToOne|OneToMany|ManyToMany|OneToOne|Embedded $annotation
     */
    private function resolveEntityClass(object $annotation, Node $node): string
    {
        if ($annotation instanceof Embedded) {
            return $this->resolveFqnTargetEntity($annotation->class, $node);
        }

        return $this->resolveFqnTargetEntity($annotation->targetEntity, $node);
    }
}
