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

This module is tested using automated tests implemented with the **Cypress** framework. To set up Cypress, refer to the following repository:  
https://github.com/vanderbilt-redcap/redcap_cypress

We use a custom Docker instance, **CCTC_REDCap_Docker**, instead of `redcap_docker`. This instance mirrors our Live environment by using the same versions of **MariaDB** and **PHP**.

All automated test scripts are located in the `automated_tests` directory. These scripts can also be used by users to manually test the external module. The directory contains:
- Custom step definitions created by our team
- Fixture files
- User Requirement Specification (URS) documents
- Feature test scripts