Feature: E.123.1200 - The system shall record configuration changes for the Embellish Fields external module (who, when, old->new) to the module's View Logs page.

  As a REDCap administrator
  I want every configuration change to be written to the module's External Module Logs
  So that there is an audit trail of who changed which setting, when, and from what value to what.

  Scenario: Enable external module from Control Center
    Given I login to REDCap with the user "Test_Admin"
    When I click on the link labeled "Control Center"
    And I click on the link labeled "Manage"
    Then I should see "External Modules - Module Manager"
    And I should NOT see "Embellish fields - v1.1.0"
    When I click on the button labeled "Enable a module"
    And I wait for 2 seconds
    Then I should see "Available Modules"
    And I click on the button labeled "Enable" in the row labeled "Embellish fields"
    And I wait for 1 second
    And I click on the button labeled "Enable"
    Then I should see "Embellish fields - v1.1.0"

  Scenario: First configuration save logs the initial values
    Given I create a new project named "E.123.1200" by clicking on "New Project" in the menu bar, selecting "Practice / Just for fun" from the dropdown, choosing file "fixtures/cdisc_files/Project_redcap_val_nodata.xml", and clicking the "Create Project" button
    And I click on the link labeled "Manage"
    Then I should see "External Modules - Project Module Manager"
    When I click on the button labeled "Enable a module"
    And I click on the button labeled "Enable" in the row labeled "Embellish fields - v1.1.0"
    Then I should see "Embellish fields - v1.1.0"

    # First save has no prior snapshot, so each setting the admin actually sets is
    # logged as (empty) -> value. Blank settings stay empty and are not logged.
    And I click on the button labeled "Configure"
    When I check the checkbox labeled "When checked, the field variable name will be shown"
    And I check the checkbox labeled "When checked, using action tags on questions that match the regular expression"
    And I enter "@FOO" into the textarea field labeled "A regular expression"
    Then I click on the button labeled "Save"
    And I should see "Embellish fields - v1.1.0"

    #VERIFY - the audit trail on the module's own View Logs page
    When I click on the link labeled "View Logs"
    Then I should see "External Module Logs"
    And I should see a table header and row containing the following values in a table:
      | Module           | Message                         | UserName   |
      | embellish_fields | Configuration changed (project) | Test_Admin |

    # Newest entry (first button): the action-tag regex set to @FOO ((empty) -> @FOO).
    # old->new values are stored as params 'old_value'/'new_value'; 'setting' names
    # the changed key. The acting user is the UserName column, not a param.
    When I click on the first button labeled "Show Parameters"
    Then I should see "Log Entry Parameters"
    And I should see a table header and row containing the following values in a table:
      | Name      | Value            |
      | setting   | action-tag-regex |
      | old_value | (empty)          |
      | new_value | @FOO             |

    # Dismiss the parameters dialog and return to the log list
    And I click on the button labeled "Close"
    Then I should see "External Module Logs"
    And I should see a table header and row containing the following values in a table:
      | Module           | Message                         | UserName   |
      | embellish_fields | Configuration changed (project) | Test_Admin |

    # Second entry (second button): the "include action tags" checkbox turned on ((empty) -> 1)
    When I click on the second button labeled "Show Parameters"
    Then I should see "Log Entry Parameters"
    And I should see a table header and row containing the following values in a table:
      | Name      | Value               |
      | setting   | include-action-tags |
      | old_value | (empty)             |
      | new_value | 1                   |

  Scenario: Changing a setting logs an old->new audit entry
    # rctf starts each scenario from a clean browser page, so re-navigate to the
    # project fresh (same pattern as E.123.700's continuation scenarios).
    Given I login to REDCap with the user "Test_Admin"
    When I click on the link labeled "My Projects"
    And I click on the link labeled "E.123.1200"
    And I click on the link labeled "Manage"
    Then I should see "External Modules - Project Module Manager"
    And I should see "Embellish fields - v1.1.0"

    # Change the action-tag regex from @FOO to @BAR. old->new genuinely matters
    # for this non-binary text setting, unlike the checkboxes.
    When I click on the button labeled "Configure"
    And I clear field and enter "@BAR" into the textarea field labeled "A regular expression"
    Then I click on the button labeled "Save"
    And I should see "Embellish fields - v1.1.0"

    #VERIFY - the audit trail on the module's own View Logs page
    When I click on the link labeled "View Logs"
    Then I should see "External Module Logs"
    And I should see a table header and row containing the following values in a table:
      | Module           | Message                         | UserName   |
      | embellish_fields | Configuration changed (project) | Test_Admin |

    # old->new values live in admin-gated parameters (safe for repos whose
    # settings hold secrets). The most recent entry is the @FOO -> @BAR change.
    When I click on the first button labeled "Show Parameters"
    Then I should see "Log Entry Parameters"
    And I should see a table header and row containing the following values in a table:
      | Name      | Value            |
      | setting   | action-tag-regex |
      | old_value | @FOO             |
      | new_value | @BAR             |

    # Verify no exceptions are thrown in the system
    Given I open Email
    Then I should NOT see an email with subject "REDCap External Module Hook Exception - embellish_fields"