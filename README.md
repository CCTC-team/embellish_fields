### Embellish fields

[![Embellish Fields EM Cypress Tests](https://github.com/CCTC-team/embellish_fields/actions/workflows/cypress-tests.yml/badge.svg)](https://github.com/CCTC-team/embellish_fields/actions/workflows/cypress-tests.yml)

The Embellish fields module is a simple module that can provide extra information to users about the fields in a data 
entry form. The extra information is provided underneath the field label as a pipe-separated list

#### Set up and configuration

Settings are enabled at a project level and are as follows;

- `show-field-variable-name` - when checked, the field name is shown
- `show-field-element-type` - when checked, the field element type is shown. Note, this is different to the validation
  type as in the following setting
- `show-field-validation-type` - when checked, the field validation type is shown - for example, for integer values,
  shows 'int'
- `include-action-tags` - when checked, ActionTags that match the regular expression in the next setting are also shown
- `action-tag-regex` - a regular expression that determines which action tags are shown. For example, including the
    regular expression ```@ENDPOINT-\w+``` will show any ActionTags that begin with @ENDPOINT - e.g. @ENDPOINT-PRIMARY
   and @ENDPOINT-SAFETY. The regular expression should NOT include the usual surrounding '/'s

##### Configuration audit log

Every change to the module's configuration is recorded to the module's **View Logs** page (Control Center →
External Modules → View Logs). One entry is written per changed setting, recording the setting name, its old and
new values, the user who made the change and when. The first save on a freshly configured module logs the values
that were actually set (as `(empty) -> value`); settings left blank are not logged. Old and new values are held as
log parameters, which REDCap shows to super-users via the **Show Parameters** button.

#### Automation Testing

The `automated_tests` directory contains the User Requirement Specification (URS) documents, Cypress/Gherkin feature scripts and fixture files for this module. The scripts can also be followed to test the module manually. To run them, see [Setup_Overview.md](https://github.com/CCTC-team/CCTC_REDCap_Docker/blob/redcap_val/Setup_Overview.md).

---

## Who are we

The Cambridge Cancer Trials Centre (CCTC) is a collaboration between Cambridge University Hospitals NHS Foundation Trust, the University of Cambridge, and Cancer Research UK. Founded in 2007, CCTC designs and conducts clinical trials and studies to improve outcomes for patients with cancer or those at risk of developing it. In 2011, CCTC began hosting the Cambridge Clinical Trials Unit - Cancer Theme (CCTU-CT).

CCTC has two divisions: Cancer Theme, which coordinates trial delivery, and Clinical Operations.