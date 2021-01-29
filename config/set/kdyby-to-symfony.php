<?php

declare(strict_types=1);

use Rector\Core\ValueObject\Visibility;
use Rector\NetteToSymfony\Rector\MethodCall\WrapTransParameterNameRector;
use Rector\Renaming\Rector\MethodCall\RenameMethodRector;
use Rector\Renaming\Rector\Name\RenameClassRector;
use Rector\Renaming\ValueObject\MethodCallRename;
use Rector\Visibility\Rector\ClassMethod\ChangeMethodVisibilityRector;
use Rector\Visibility\ValueObject\ChangeMethodVisibility;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symplify\SymfonyPhpConfig\ValueObjectInliner;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(ChangeMethodVisibilityRector::class)
        ->call('configure', [[
            ChangeMethodVisibilityRector::METHOD_VISIBILITIES => ValueObjectInliner::inline([
                new ChangeMethodVisibility('Kdyby\Events\Subscriber', 'getSubscribedEvents', Visibility::STATIC),
            ]),
        ]]);

    $services->set(RenameMethodRector::class)
        ->call('configure', [[
            RenameMethodRector::METHOD_CALL_RENAMES => ValueObjectInliner::inline([
                new MethodCallRename('Kdyby\Translation\Translator', 'translate', 'trans'),
                new MethodCallRename('Kdyby\RabbitMq\IConsumer', 'process', 'execute'),
            ]),
        ]]);

    $services->set(RenameClassRector::class)
        ->call('configure', [[
            RenameClassRector::OLD_TO_NEW_CLASSES => [
                'Kdyby\RabbitMq\IConsumer' => 'OldSound\RabbitMqBundle\RabbitMq\ConsumerInterface',
                'Kdyby\RabbitMq\IProducer' => 'OldSound\RabbitMqBundle\RabbitMq\ProducerInterface',
                'Kdyby\Monolog\Logger' => 'Psr\Log\LoggerInterface',
                'Kdyby\Events\Subscriber' => 'Symfony\Component\EventDispatcher\EventSubscriberInterface',
                'Kdyby\Translation\Translator' => 'Symfony\Contracts\Translation\TranslatorInterface',
            ],
        ]]);

    $services->set(WrapTransParameterNameRector::class);
};
