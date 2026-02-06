# Embellish Fields External Module

## Overview

The Embellish Fields module enhances REDCap data entry forms by displaying additional metadata information alongside each field. This information appears as a pipe-separated list below field labels, helping users quickly identify field properties without navigating to the data dictionary.

## Module Information

- **Namespace**: `CCTC\EmbellishFieldsModule`
- **Framework Version**: 14
- **Documentation**: https://github.com/CCTC/embellish_fields
- **Authors**:
  - Richard Hardy (University of Cambridge - Cambridge Cancer Trials Centre)
  - Mintoo Xavier (Cambridge University Hospital - Cambridge Cancer Trials Centre)

## Compatibility

| Requirement | Minimum Version | Maximum Version |
|-------------|-----------------|-----------------|
| PHP | 8.0.27 | 8.2.29 |
| REDCap | 13.8.1 | 15.9.1 |

## Features

The module can display the following information for each field on data entry forms:

1. **Field Variable Name** - The internal variable name used in the data dictionary
2. **Field Element Type** - The type of field (text, radio, checkbox, dropdown, etc.)
3. **Field Validation Type** - The validation applied to the field (int, date, email, etc.)
4. **Action Tags** - Custom action tags matching a configurable regex pattern

## Configuration

All settings are configured at the **project level** and are restricted to **super users only**.

### Project Settings

| Setting | Key | Description |
|---------|-----|-------------|
| Show Field Variable Name | `show-field-variable-name` | Displays the field's variable name |
| Show Field Element Type | `show-field-element-type` | Displays the field type (text, radio, etc.) |
| Show Field Validation Type | `show-field-validation-type` | Displays validation type (int, date, etc.) |
| Include Action Tags | `include-action-tags` | Enables action tag display based on regex |
| Action Tag Regex | `action-tag-regex` | Regular expression to filter which action tags to display (conditionally shown) |

### Conditional Settings (Branching Logic)

The **Action Tag Regex** field is only visible when **Include Action Tags** is checked. This uses the `branchingLogic` feature in `config.json` to reduce configuration clutter when action tag display is not needed.

### Configuration Requirements

- At least one display option must be enabled for the module to function
- If "Include Action Tags" is enabled, the "Action Tag Regex" field must be configured

## Usage

### Basic Setup

1. Enable the module for your project
2. Navigate to the module's project settings
3. Check the information types you want to display
4. Save settings

### Action Tag Filtering Example

To display specific action tags, enable "Include Action Tags" and configure a regex pattern:

- **Pattern**: `@ENDPOINT-\w+`
- **Matches**: `@ENDPOINT-PRIMARY`, `@ENDPOINT-SAFETY`, `@ENDPOINT-SECONDARY`

**Note**: Do not include surrounding `/` delimiters in the regex pattern.

### Display Format

Information appears below each field label in a styled div with the following format:

```
field_name | text | int | @CUSTOM-TAG
```

The display uses a blue color (#3a71a5) and smaller font size for visual distinction from the field label. Styling is defined in `css/embellish.css` and can be customized.

## Technical Details

### Hook Used

- `redcap_data_entry_form` - Executes when a data entry form is rendered

### File Structure

```
embellish_fields_v1.0.0/
├── EmbellishFieldsModule.php   # Main module class
├── config.json                 # Module configuration
├── css/
│   └── embellish.css          # External stylesheet for field info display
├── js/
│   └── embellish.js           # External JavaScript for DOM manipulation
├── README.md                   # Basic readme
├── LICENSE                     # License file
├── DOCUMENTATION.md            # This file
├── IMPROVEMENT_PLAN.md         # Planned improvements and status
└── automated_tests/           # Test files
    ├── fixtures/              # Test fixtures (CDISC XML files)
    ├── step_definitions/      # Cypress step definitions
    └── urs/                   # User Requirement Specification
```

### External Assets

JavaScript and CSS are loaded as external files rather than inline, improving maintainability and enabling browser caching:

- **`js/embellish.js`** - Contains the `AddTag()` function for inserting field metadata into the DOM, and an `escapeHtml()` helper for client-side XSS prevention
- **`css/embellish.css`** - Defines the `.embellish-field-info` styles for the metadata display

These files are loaded via the framework's `$this->getUrl()` method, ensuring correct URL resolution.

### Security Features

The following security measures have been implemented (based on Phase 1 of the improvement plan):

- **XSS Prevention (Server-side)**: Field names and action tag values are encoded with `json_encode()` before being injected into JavaScript, preventing script injection via malicious field names or action tag content
- **XSS Prevention (Client-side)**: The `escapeHtml()` function in `js/embellish.js` escapes HTML entities before inserting content into the DOM
- **Regex Validation**: Regex patterns from project settings are validated with a test `preg_match()` call before use. Invalid patterns are logged with `$this->log()` and skipped, preventing PHP warnings or errors
- **HTML Entity Escaping**: Action tag matches are escaped with `htmlspecialchars($match, ENT_QUOTES, 'UTF-8')` before display

### Settings Handling

Project settings for checkboxes use `(bool)` casting for reliable handling, as REDCap checkbox settings may return `true`, `"true"`, `1`, or `"1"` depending on context:

```php
$includeFieldName = (bool)$this->getProjectSetting('show-field-variable-name');
```

## Validation

The module validates settings on save:

1. Ensures at least one display option is selected
2. Requires action tag regex when action tag display is enabled

At runtime, regex pattern syntax is also validated before processing fields.

## Example Output

When configured to show field variable name and validation type, a field might display:

```
Patient Age: [_____]
patient_age | int
```

## Completed Improvements

The following improvements from the [IMPROVEMENT_PLAN.md](IMPROVEMENT_PLAN.md) have been implemented:

### Phase 1: Critical Security Fixes
| Item | Status | Description |
|------|--------|-------------|
| 1.1 XSS Vulnerability Fix | Completed | Field names and action tags are `json_encode()`-ed before JavaScript injection |
| 1.2 Regex Validation | Completed | Invalid regex patterns are caught, logged, and skipped |

### Phase 2: Configuration Improvements
| Item | Status | Description |
|------|--------|-------------|
| Documentation URL | Completed | Added to `config.json` |
| Branching Logic | Completed | Action Tag Regex field is conditionally shown based on Include Action Tags |
| Clean up empty arrays | Completed | Removed unused `links` and `system-settings` arrays from `config.json` |
| Permissions declaration | Not yet | `permissions` array not yet added to `config.json` |

### Phase 3: Code Quality
| Item | Status | Description |
|------|--------|-------------|
| 3.1 Boolean comparisons | Completed | Settings use `(bool)` cast instead of string comparison |
| 3.2 Remove redundant alert | Not yet | Runtime alert for no settings configured is still present |
| 3.3 Replace global $Proj | Not yet | Still using `global $Proj` for field metadata access |
| 3.4 Externalize JavaScript | Completed | `js/embellish.js` created with `AddTag()` and `escapeHtml()` |
| 3.5 Externalize CSS | Completed | `css/embellish.css` created with `.embellish-field-info` styles |

### Phase 4: Feature Enhancements
| Item | Status | Description |
|------|--------|-------------|
| 4.1 Survey support | Not yet | Only `redcap_data_entry_form` hook is implemented |
| 4.2 Shared renderEmbellishments() | Not yet | Logic remains in the hook method directly |
| 4.3 Debug logging | Not yet | No system-level debug logging setting |
| 4.4 PHPDoc comments | Not yet | No PHPDoc annotations added to PHP class |

## Troubleshooting

### "Please ensure at least one setting is configured"

This alert appears at runtime when no display options are enabled. Enable at least one checkbox in the module settings. (Note: the `validateSettings()` method also prevents saving with no options selected.)

### Action tags not displaying

1. Verify "Include Action Tags" is checked
2. Verify the regex pattern is correct
3. Check that action tags in your fields match the regex pattern
4. Ensure regex does not include surrounding `/` delimiters

### Invalid regex pattern

If a regex pattern is invalid, the module will log the error and skip action tag processing. Check the REDCap external module logs for details and correct your pattern syntax.
