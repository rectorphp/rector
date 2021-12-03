<?php

declare(strict_types=1);

use Rector\Core\Configuration\Option;
use Rector\Core\ValueObject\PhpVersionFeature;
use Rector\Php80\Rector\Class_\AnnotationToAttributeRector;
use Rector\Php80\ValueObject\AnnotationToAttribute;
use Rector\Tests\Php80\Rector\Class_\AnnotationToAttributeRector\Source\Annotation\Apple;
use Rector\Tests\Php80\Rector\Class_\AnnotationToAttributeRector\Source\Attribute\Apple as AppleAttribute;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $parameters = $containerConfigurator->parameters();
    $parameters->set(Option::AUTO_IMPORT_NAMES, true);
    $parameters->set(Option::PHP_VERSION_FEATURES, PhpVersionFeature::NEW_INITIALIZERS - 1);

    $services = $containerConfigurator->services();
    $services->set(AnnotationToAttributeRector::class)
        ->configure([
            new AnnotationToAttribute('Doctrine\ORM\Mapping\Entity'),
            new AnnotationToAttribute('Doctrine\ORM\Mapping\Id'),
            new AnnotationToAttribute('Doctrine\ORM\Mapping\Column'),
            new AnnotationToAttribute('Symfony\Component\Validator\Constraints\NotBlank'),
            new AnnotationToAttribute('Symfony\Component\Validator\Constraints\Length'),

            new AnnotationToAttribute(Apple::class, AppleAttribute::class),
        ]);
};
