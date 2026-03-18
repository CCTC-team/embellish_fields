### Embellish fields

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

#### Automation Testing

The module includes comprehensive **Cypress automated** tests using the **Cucumber/Gherkin framework**. To set up Cypress, refer to [Setup_Overview.md](https://github.com/CCTC-team/CCTC_REDCap_Docker/blob/redcap_val/Setup_Overview.md).

All automated test scripts are located in the `automated_tests` directory. The test suite automatically picks up the scripts from this folder. These scripts can also be used to manually test the external module. The directory contains:
- Custom step definitions created by our team
- Fixture files
- User Requirement Specification (URS) documents
- Feature test scripts

**Step Definition Locations:**

Step definitions are organized across multiple locations in the `redcap_cypress` repo under `redcap_cypress/cypress/support/step_definitions/`:

- **Non-core feature step definitions** are in `redcap_cypress/cypress/support/step_definitions/noncore.js`
- **Shared EM step definitions** (used by more than one external module) are in `redcap_cypress/cypress/support/step_definitions/external_module.js`

---

## Who are we

The Cambridge Cancer Trials Centre (CCTC) is a collaboration between Cambridge University Hospitals NHS Foundation Trust, the University of Cambridge, and Cancer Research UK. Founded in 2007, CCTC designs and conducts clinical trials and studies to improve outcomes for patients with cancer or those at risk of developing it. In 2011, CCTC began hosting the Cambridge Clinical Trials Unit - Cancer Theme (CCTU-CT).

CCTC has two divisions: Cancer Theme, which coordinates trial delivery, and Clinical Operations.