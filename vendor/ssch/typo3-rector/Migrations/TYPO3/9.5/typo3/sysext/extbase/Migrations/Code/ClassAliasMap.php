<?php

namespace RectorPrefix20210519;

return [
    // TYPO3 v8 replacements
    'RectorPrefix20210519\\TYPO3\\CMS\\Extbase\\Service\\TypoScriptService' => 'RectorPrefix20210519\\TYPO3\\CMS\\Core\\TypoScript\\TypoScriptService',
    // TYPO3 v9 replacements
    // Configuration
    'RectorPrefix20210519\\TYPO3\\CMS\\Extbase\\Configuration\\Exception\\ContainerIsLockedException' => 'RectorPrefix20210519\\TYPO3\\CMS\\Extbase\\Configuration\\Exception',
    'RectorPrefix20210519\\TYPO3\\CMS\\Extbase\\Configuration\\Exception\\NoSuchFileException' => 'RectorPrefix20210519\\TYPO3\\CMS\\Extbase\\Configuration\\Exception',
    'RectorPrefix20210519\\TYPO3\\CMS\\Extbase\\Configuration\\Exception\\NoSuchOptionException' => 'RectorPrefix20210519\\TYPO3\\CMS\\Extbase\\Configuration\\Exception',
    // no proper fallback
    'RectorPrefix20210519\\TYPO3\\CMS\\Extbase\\Mvc\\Exception\\InvalidMarkerException' => 'RectorPrefix20210519\\TYPO3\\CMS\\Extbase\\Exception',
    'RectorPrefix20210519\\TYPO3\\CMS\\Extbase\\Mvc\\Exception\\InvalidRequestTypeException' => 'RectorPrefix20210519\\TYPO3\\CMS\\Extbase\\Mvc\\Exception',
    'RectorPrefix20210519\\TYPO3\\CMS\\Extbase\\Mvc\\Exception\\RequiredArgumentMissingException' => 'RectorPrefix20210519\\TYPO3\\CMS\\Extbase\\Mvc\\Exception',
    'RectorPrefix20210519\\TYPO3\\CMS\\Extbase\\Mvc\\Exception\\InvalidCommandIdentifierException' => 'RectorPrefix20210519\\TYPO3\\CMS\\Extbase\\Mvc\\Exception',
    'RectorPrefix20210519\\TYPO3\\CMS\\Extbase\\Mvc\\Exception\\InvalidOrNoRequestHashException' => 'RectorPrefix20210519\\TYPO3\\CMS\\Extbase\\Security\\Exception\\InvalidHashException',
    'RectorPrefix20210519\\TYPO3\\CMS\\Extbase\\Mvc\\Exception\\InvalidUriPatternException' => 'RectorPrefix20210519\\TYPO3\\CMS\\Extbase\\Security\\Exception',
    // Object Container
    'RectorPrefix20210519\\TYPO3\\CMS\\Extbase\\Object\\Container\\Exception\\CannotInitializeCacheException' => 'RectorPrefix20210519\\TYPO3\\CMS\\Core\\Cache\\Exception\\InvalidCacheException',
    'RectorPrefix20210519\\TYPO3\\CMS\\Extbase\\Object\\Container\\Exception\\TooManyRecursionLevelsException' => 'RectorPrefix20210519\\TYPO3\\CMS\\Extbase\\Object\\Exception',
    // ObjectManager
    'RectorPrefix20210519\\TYPO3\\CMS\\Extbase\\Object\\Exception\\WrongScopeException' => 'RectorPrefix20210519\\TYPO3\\CMS\\Extbase\\Object\\Exception',
    'RectorPrefix20210519\\TYPO3\\CMS\\Extbase\\Object\\InvalidClassException' => 'RectorPrefix20210519\\TYPO3\\CMS\\Extbase\\Object\\Exception',
    'RectorPrefix20210519\\TYPO3\\CMS\\Extbase\\Object\\InvalidObjectConfigurationException' => 'RectorPrefix20210519\\TYPO3\\CMS\\Extbase\\Object\\Exception',
    'RectorPrefix20210519\\TYPO3\\CMS\\Extbase\\Object\\InvalidObjectException' => 'RectorPrefix20210519\\TYPO3\\CMS\\Extbase\\Object\\Exception',
    'RectorPrefix20210519\\TYPO3\\CMS\\Extbase\\Object\\ObjectAlreadyRegisteredException' => 'RectorPrefix20210519\\TYPO3\\CMS\\Extbase\\Object\\Exception',
    'RectorPrefix20210519\\TYPO3\\CMS\\Extbase\\Object\\UnknownClassException' => 'RectorPrefix20210519\\TYPO3\\CMS\\Extbase\\Object\\Exception',
    'RectorPrefix20210519\\TYPO3\\CMS\\Extbase\\Object\\UnknownInterfaceException' => 'RectorPrefix20210519\\TYPO3\\CMS\\Extbase\\Object\\Exception',
    'RectorPrefix20210519\\TYPO3\\CMS\\Extbase\\Object\\UnresolvedDependenciesException' => 'RectorPrefix20210519\\TYPO3\\CMS\\Extbase\\Object\\Exception',
    // Persistence
    'RectorPrefix20210519\\TYPO3\\CMS\\Extbase\\Persistence\\Generic\\Exception\\CleanStateNotMemorizedException' => 'RectorPrefix20210519\\TYPO3\\CMS\\Extbase\\Persistence\\Generic\\Exception',
    'RectorPrefix20210519\\TYPO3\\CMS\\Extbase\\Persistence\\Generic\\Exception\\InvalidPropertyTypeException' => 'RectorPrefix20210519\\TYPO3\\CMS\\Extbase\\Persistence\\Generic\\Exception',
    'RectorPrefix20210519\\TYPO3\\CMS\\Extbase\\Persistence\\Generic\\Exception\\MissingBackendException' => 'RectorPrefix20210519\\TYPO3\\CMS\\Extbase\\Persistence\\Generic\\Exception',
    // Property
    'RectorPrefix20210519\\TYPO3\\CMS\\Extbase\\Property\\Exception\\FormatNotSupportedException' => 'RectorPrefix20210519\\TYPO3\\CMS\\Extbase\\Property\\Exception',
    'RectorPrefix20210519\\TYPO3\\CMS\\Extbase\\Property\\Exception\\InvalidFormatException' => 'RectorPrefix20210519\\TYPO3\\CMS\\Extbase\\Property\\Exception',
    'RectorPrefix20210519\\TYPO3\\CMS\\Extbase\\Property\\Exception\\InvalidPropertyException' => 'RectorPrefix20210519\\TYPO3\\CMS\\Extbase\\Property\\Exception',
    // Reflection
    'RectorPrefix20210519\\TYPO3\\CMS\\Extbase\\Reflection\\Exception\\InvalidPropertyTypeException' => 'RectorPrefix20210519\\TYPO3\\CMS\\Extbase\\Reflection\\Exception',
    // Security
    'RectorPrefix20210519\\TYPO3\\CMS\\Extbase\\Security\\Exception\\InvalidArgumentForRequestHashGenerationException' => 'RectorPrefix20210519\\TYPO3\\CMS\\Extbase\\Security\\Exception',
    'RectorPrefix20210519\\TYPO3\\CMS\\Extbase\\Security\\Exception\\SyntacticallyWrongRequestHashException' => 'RectorPrefix20210519\\TYPO3\\CMS\\Extbase\\Security\\Exception',
    // Validation
    'RectorPrefix20210519\\TYPO3\\CMS\\Extbase\\Validation\\Exception\\InvalidSubjectException' => 'RectorPrefix20210519\\TYPO3\\CMS\\Extbase\\Validation\\Exception',
    'RectorPrefix20210519\\TYPO3\\CMS\\Extbase\\Validation\\Exception\\NoValidatorFoundException' => 'RectorPrefix20210519\\TYPO3\\CMS\\Extbase\\Validation\\Exception',
    // Fluid
    'RectorPrefix20210519\\TYPO3\\CMS\\Extbase\\Mvc\\Exception\\InvalidViewHelperException' => 'RectorPrefix20210519\\TYPO3\\CMS\\Extbase\\Exception',
    'RectorPrefix20210519\\TYPO3\\CMS\\Extbase\\Mvc\\Exception\\InvalidTemplateResourceException' => 'RectorPrefix20210519\\TYPO3Fluid\\Fluid\\View\\Exception\\InvalidTemplateResourceException',
    // Service
    'RectorPrefix20210519\\TYPO3\\CMS\\Extbase\\Service\\FlexFormService' => 'RectorPrefix20210519\\TYPO3\\CMS\\Core\\Service\\FlexFormService',
];
