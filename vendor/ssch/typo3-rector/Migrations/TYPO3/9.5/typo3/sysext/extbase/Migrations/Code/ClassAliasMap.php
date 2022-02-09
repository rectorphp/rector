<?php

namespace RectorPrefix20220209;

return [
    // TYPO3 v8 replacements
    'TYPO3\\CMS\\Extbase\\Service\\TypoScriptService' => 'TYPO3\\CMS\\Core\\TypoScript\\TypoScriptService',
    // TYPO3 v9 replacements
    // Configuration
    'TYPO3\\CMS\\Extbase\\Configuration\\Exception\\ContainerIsLockedException' => 'TYPO3\\CMS\\Extbase\\Configuration\\Exception',
    'TYPO3\\CMS\\Extbase\\Configuration\\Exception\\NoSuchFileException' => 'TYPO3\\CMS\\Extbase\\Configuration\\Exception',
    'TYPO3\\CMS\\Extbase\\Configuration\\Exception\\NoSuchOptionException' => 'TYPO3\\CMS\\Extbase\\Configuration\\Exception',
    // no proper fallback
    'TYPO3\\CMS\\Extbase\\Mvc\\Exception\\InvalidMarkerException' => 'TYPO3\\CMS\\Extbase\\Exception',
    'TYPO3\\CMS\\Extbase\\Mvc\\Exception\\InvalidRequestTypeException' => 'TYPO3\\CMS\\Extbase\\Mvc\\Exception',
    'TYPO3\\CMS\\Extbase\\Mvc\\Exception\\RequiredArgumentMissingException' => 'TYPO3\\CMS\\Extbase\\Mvc\\Exception',
    'TYPO3\\CMS\\Extbase\\Mvc\\Exception\\InvalidCommandIdentifierException' => 'TYPO3\\CMS\\Extbase\\Mvc\\Exception',
    'TYPO3\\CMS\\Extbase\\Mvc\\Exception\\InvalidOrNoRequestHashException' => 'TYPO3\\CMS\\Extbase\\Security\\Exception\\InvalidHashException',
    'TYPO3\\CMS\\Extbase\\Mvc\\Exception\\InvalidUriPatternException' => 'TYPO3\\CMS\\Extbase\\Security\\Exception',
    // Object Container
    'TYPO3\\CMS\\Extbase\\Object\\Container\\Exception\\CannotInitializeCacheException' => 'TYPO3\\CMS\\Core\\Cache\\Exception\\InvalidCacheException',
    'TYPO3\\CMS\\Extbase\\Object\\Container\\Exception\\TooManyRecursionLevelsException' => 'TYPO3\\CMS\\Extbase\\Object\\Exception',
    // ObjectManager
    'TYPO3\\CMS\\Extbase\\Object\\Exception\\WrongScopeException' => 'TYPO3\\CMS\\Extbase\\Object\\Exception',
    'TYPO3\\CMS\\Extbase\\Object\\InvalidClassException' => 'TYPO3\\CMS\\Extbase\\Object\\Exception',
    'TYPO3\\CMS\\Extbase\\Object\\InvalidObjectConfigurationException' => 'TYPO3\\CMS\\Extbase\\Object\\Exception',
    'TYPO3\\CMS\\Extbase\\Object\\InvalidObjectException' => 'TYPO3\\CMS\\Extbase\\Object\\Exception',
    'TYPO3\\CMS\\Extbase\\Object\\ObjectAlreadyRegisteredException' => 'TYPO3\\CMS\\Extbase\\Object\\Exception',
    'TYPO3\\CMS\\Extbase\\Object\\UnknownClassException' => 'TYPO3\\CMS\\Extbase\\Object\\Exception',
    'TYPO3\\CMS\\Extbase\\Object\\UnknownInterfaceException' => 'TYPO3\\CMS\\Extbase\\Object\\Exception',
    'TYPO3\\CMS\\Extbase\\Object\\UnresolvedDependenciesException' => 'TYPO3\\CMS\\Extbase\\Object\\Exception',
    // Persistence
    'TYPO3\\CMS\\Extbase\\Persistence\\Generic\\Exception\\CleanStateNotMemorizedException' => 'TYPO3\\CMS\\Extbase\\Persistence\\Generic\\Exception',
    'TYPO3\\CMS\\Extbase\\Persistence\\Generic\\Exception\\InvalidPropertyTypeException' => 'TYPO3\\CMS\\Extbase\\Persistence\\Generic\\Exception',
    'TYPO3\\CMS\\Extbase\\Persistence\\Generic\\Exception\\MissingBackendException' => 'TYPO3\\CMS\\Extbase\\Persistence\\Generic\\Exception',
    // Property
    'TYPO3\\CMS\\Extbase\\Property\\Exception\\FormatNotSupportedException' => 'TYPO3\\CMS\\Extbase\\Property\\Exception',
    'TYPO3\\CMS\\Extbase\\Property\\Exception\\InvalidFormatException' => 'TYPO3\\CMS\\Extbase\\Property\\Exception',
    'TYPO3\\CMS\\Extbase\\Property\\Exception\\InvalidPropertyException' => 'TYPO3\\CMS\\Extbase\\Property\\Exception',
    // Reflection
    'TYPO3\\CMS\\Extbase\\Reflection\\Exception\\InvalidPropertyTypeException' => 'TYPO3\\CMS\\Extbase\\Reflection\\Exception',
    // Security
    'TYPO3\\CMS\\Extbase\\Security\\Exception\\InvalidArgumentForRequestHashGenerationException' => 'TYPO3\\CMS\\Extbase\\Security\\Exception',
    'TYPO3\\CMS\\Extbase\\Security\\Exception\\SyntacticallyWrongRequestHashException' => 'TYPO3\\CMS\\Extbase\\Security\\Exception',
    // Validation
    'TYPO3\\CMS\\Extbase\\Validation\\Exception\\InvalidSubjectException' => 'TYPO3\\CMS\\Extbase\\Validation\\Exception',
    'TYPO3\\CMS\\Extbase\\Validation\\Exception\\NoValidatorFoundException' => 'TYPO3\\CMS\\Extbase\\Validation\\Exception',
    // Fluid
    'TYPO3\\CMS\\Extbase\\Mvc\\Exception\\InvalidViewHelperException' => 'TYPO3\\CMS\\Extbase\\Exception',
    'TYPO3\\CMS\\Extbase\\Mvc\\Exception\\InvalidTemplateResourceException' => 'TYPO3Fluid\\Fluid\\View\\Exception\\InvalidTemplateResourceException',
    // Service
    'TYPO3\\CMS\\Extbase\\Service\\FlexFormService' => 'TYPO3\\CMS\\Core\\Service\\FlexFormService',
];
