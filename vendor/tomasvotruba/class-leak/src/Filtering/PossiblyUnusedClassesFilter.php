<?php

declare (strict_types=1);
namespace RectorPrefix202607\TomasVotruba\ClassLeak\Filtering;

use RectorPrefix202607\TomasVotruba\ClassLeak\ValueObject\FileWithClass;
use RectorPrefix202607\Webmozart\Assert\Assert;
final class PossiblyUnusedClassesFilter
{
    /**
     * These class types are used by some kind of collector pattern. Either loaded magically, registered only in config,
     * an entry point or a tagged extensions.
     *
     * @var string[]
     */
    private const DEFAULT_TYPES_TO_SKIP = [
        // http-kernel
        'RectorPrefix202607\Symfony\Component\Console\Application',
        'RectorPrefix202607\Symfony\Component\HttpKernel\DependencyInjection\Extension',
        'RectorPrefix202607\Symfony\Bundle\FrameworkBundle\Controller\Controller',
        'RectorPrefix202607\Symfony\Bundle\FrameworkBundle\Controller\AbstractController',
        'RectorPrefix202607\Livewire\Component',
        'RectorPrefix202607\Illuminate\Routing\Controller',
        'RectorPrefix202607\Illuminate\Contracts\Http\Kernel',
        'RectorPrefix202607\Illuminate\Support\ServiceProvider',
        // events
        'RectorPrefix202607\Symfony\Component\EventDispatcher\EventSubscriberInterface',
        'RectorPrefix202607\Symfony\Component\Form\FormTypeExtensionInterface',
        'RectorPrefix202607\Symfony\Component\Security\Core\Authentication\SimpleAuthenticatorInterface',
        'RectorPrefix202607\Vich\UploaderBundle\Naming\DirectoryNamerInterface',
        // validator
        'RectorPrefix202607\Symfony\Component\Validator\Constraint',
        'RectorPrefix202607\Symfony\Component\Validator\ConstraintValidator',
        'RectorPrefix202607\Symfony\Component\Validator\ConstraintValidatorInterface',
        'RectorPrefix202607\Symfony\Component\Security\Core\Authorization\Voter\VoterInterface',
        'RectorPrefix202607\Symfony\Component\Security\Http\Logout\LogoutSuccessHandlerInterface',
        'RectorPrefix202607\Symfony\Component\Security\Http\Authentication\AuthenticationSuccessHandlerInterface',
        'RectorPrefix202607\Symfony\Component\Security\Http\Authorization\AccessDeniedHandlerInterface',
        'RectorPrefix202607\Symfony\Component\Security\Http\Authentication\AuthenticationFailureHandlerInterface',
        // symfony forms
        'RectorPrefix202607\Symfony\Component\ExpressionLanguage\ExpressionFunctionProviderInterface',
        'RectorPrefix202607\Symfony\Component\Form\AbstractType',
        // doctrine
        'RectorPrefix202607\Doctrine\Common\DataFixtures\FixtureInterface',
        'RectorPrefix202607\Doctrine\Common\EventSubscriber',
        'RectorPrefix202607\Nelmio\Alice\ProcessorInterface',
        // kernel
        'RectorPrefix202607\Symfony\Component\HttpKernel\Bundle\BundleInterface',
        'RectorPrefix202607\Symfony\Component\HttpKernel\KernelInterface',
        'RectorPrefix202607\Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator',
        // console
        'RectorPrefix202607\Symfony\Component\Console\Command\Command',
        'RectorPrefix202607\Entropy\Console\Contract\CommandInterface',
        'RectorPrefix202607\Twig\Extension\ExtensionInterface',
        'RectorPrefix202607\PhpCsFixer\Fixer\FixerInterface',
        'RectorPrefix202607\PHPUnit\Framework\TestCase',
        'PHPStan\Rules\Rule',
        'PHPStan\Command\ErrorFormatter\ErrorFormatter',
        // tests
        'RectorPrefix202607\Behat\Behat\Context\Context',
        // jms
        'RectorPrefix202607\JMS\Serializer\Handler\SubscribingHandlerInterface',
        // laravel
        'RectorPrefix202607\Illuminate\Support\ServiceProvider',
        'RectorPrefix202607\Illuminate\Foundation\Http\Kernel',
        'RectorPrefix202607\Illuminate\Contracts\Console\Kernel',
        'RectorPrefix202607\Illuminate\Routing\Controller',
        // Doctrine
        'RectorPrefix202607\Doctrine\Migrations\AbstractMigration',
    ];
    /**
     * @var string[]
     */
    private const DEFAULT_ATTRIBUTES_TO_SKIP = [
        // Symfony
        'RectorPrefix202607\Symfony\Component\Console\Attribute\AsCommand',
        'RectorPrefix202607\Symfony\Component\HttpKernel\Attribute\AsController',
        'RectorPrefix202607\Symfony\Component\EventDispatcher\Attribute\AsEventListener',
    ];
    /**
     * @param FileWithClass[] $filesWithClasses
     * @param string[] $usedClassNames
     * @param string[] $typesToSkip
     * @param string[] $suffixesToSkip
     * @param string[] $attributesToSkip
     *
     * @return FileWithClass[]
     */
    public function filter(array $filesWithClasses, array $usedClassNames, array $typesToSkip, array $suffixesToSkip, array $attributesToSkip, bool $shouldIncludeEntities): array
    {
        Assert::allString($usedClassNames);
        Assert::allString($typesToSkip);
        Assert::allString($suffixesToSkip);
        $possiblyUnusedFilesWithClasses = [];
        $typesToSkip = array_merge($typesToSkip, self::DEFAULT_TYPES_TO_SKIP);
        $attributesToSkip = array_merge($attributesToSkip, self::DEFAULT_ATTRIBUTES_TO_SKIP);
        foreach ($filesWithClasses as $fileWithClass) {
            if (in_array($fileWithClass->getClassName(), $usedClassNames, \true)) {
                continue;
            }
            // is excluded interfaces?
            if ($this->shouldSkip($fileWithClass->getClassName(), $typesToSkip)) {
                continue;
            }
            if ($shouldIncludeEntities === \false && $fileWithClass->isEntity()) {
                continue;
            }
            if ($fileWithClass->isSerialized()) {
                continue;
            }
            // is excluded suffix?
            foreach ($suffixesToSkip as $suffixToSkip) {
                if (substr_compare($fileWithClass->getClassName(), $suffixToSkip, -strlen($suffixToSkip)) === 0) {
                    continue 2;
                }
            }
            // is excluded attributes?
            foreach ($fileWithClass->getAttributes() as $attribute) {
                if ($this->shouldSkip($attribute, $attributesToSkip)) {
                    continue 2;
                }
            }
            $possiblyUnusedFilesWithClasses[] = $fileWithClass;
        }
        return $possiblyUnusedFilesWithClasses;
    }
    /**
     * @param string[] $skips
     */
    private function shouldSkip(string $type, array $skips): bool
    {
        foreach ($skips as $skip) {
            if (strpos($type, '*') === \false && is_a($type, $skip, \true)) {
                return \true;
            }
            if (fnmatch($skip, $type, \FNM_NOESCAPE)) {
                return \true;
            }
        }
        return \false;
    }
}
