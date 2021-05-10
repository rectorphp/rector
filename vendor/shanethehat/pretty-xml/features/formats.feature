Feature: PrettyXML can format XML files for common applications
  In order to have readable code
  As an application developer
  I want PrettyXML to correctly format XML for common applications

  Scenario: It can format a simple Magento config file
    Given I have a "simple magento config" xml file
    When it is formatted by PrettyXML
    Then it should be correctly formatted