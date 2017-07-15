<?php declare(strict_types=1);

use Rector\DependencyInjection\ContainerFactory;
use Symfony\Component\Console\Application;

// Performance boost
gc_disable();

// Require Composer autoload.php
require_once __DIR__ . '/bootstrap.php';

// Build DI container
$container = (new ContainerFactory())->create();

// Run Console Application
/** @var Application $application */
$application = $container->get(Application::class);
$application->run();
