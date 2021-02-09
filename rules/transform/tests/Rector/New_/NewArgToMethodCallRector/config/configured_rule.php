return static function (\Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator $containerConfigurator) : void {
    $services = $containerConfigurator->services();
    $services->set(\Rector\Transform\Rector\New_\NewArgToMethodCallRector::class)->configure('call', [[\Rector\Transform\Rector\New_\NewArgToMethodCallRector::NEW_ARGS_TO_METHOD_CALLS => \Symplify\SymfonyPhpConfig\ValueObjectInliner::inline([new \Rector\Transform\ValueObject\NewArgToMethodCall(\Rector\Transform\Tests\Rector\New_\NewArgToMethodCallRector\Source\SomeDotenv::class, true, 'usePutenv')])]]);
};