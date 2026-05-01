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

#### GitHub Actions Workflow

The module ships with a CI workflow at [.github/workflows/cypress-tests.yml](.github/workflows/cypress-tests.yml) that runs the Cypress suite end-to-end against a freshly built REDCap stack.

**Triggers**
- `push` to `main`
- Manual `workflow_dispatch`

**What it does**
1. Checks out the Embellish Fields EM (this repo) into `embellish_fields_em/`.
2. Clones the `redcap_val` branch of [`CCTC-team/redcap_cypress`](https://github.com/CCTC-team/redcap_cypress) and [`CCTC-team/CCTC_REDCap_Docker`](https://github.com/CCTC-team/CCTC_REDCap_Docker), and the matching REDCap version branch of [`CCTC-team/redcap_source`](https://github.com/CCTC-team/redcap_source).
3. Reads `redcap_version`, `mysql.docker_container`, `mysql.host`, and `mysql.port` from `cypress.env.json.example` so the rest of the job stays in sync with the Cypress config.
4. Injects this EM into `CCTC_REDCap_Docker/redcap_source/modules/embellish_fields_v1.0.1` and brings the Docker stack up (`app`, `db`, `mailhog`).
5. Configures `cypress.env.json`, points `package.json` at the CCTC-team forks of `rctf` / `redcap_rsvc`, installs Cypress, and patches an `rctf` after-run handler bug.
6. Builds the spec list from `automated_tests/E.123.*.feature` (excluding `*REDUNDANT*`) and runs them via `npm run test:retry-failed` (up to 3 attempts per spec, Chrome).
7. Merges mochawesome JSON reports and uploads test results, videos, and (on failure) screenshots as artifacts retained for 30 days.

**Required repository secrets**
- `CCTC_TEAM_PAT` — PAT with read access to the CCTC-team repos, including `redcap_source`.
- `PROJECT_ID` — Cypress Cloud project ID substituted into `cypress.config.js`.
- `CYPRESS_RECORD_KEY` — Cypress Cloud record key (recording is gated by `CYPRESS_DISABLE_RECORDING`, currently set to `1`).

**Branch / version pins** (set as `env` at the top of the workflow)
- `CCTC_DOCKER_BRANCH`, `CYPRESS_BRANCH`, `RSVC_BRANCH`, `RCTF_BRANCH` — all default to `redcap_val`.
- `EM_NAME` / `EM_VERSION` — `embellish_fields` / `v1.0.1`. Bump `EM_VERSION` when releasing a new module version so the spec glob and inject path stay aligned.

---

## Who are we

The Cambridge Cancer Trials Centre (CCTC) is a collaboration between Cambridge University Hospitals NHS Foundation Trust, the University of Cambridge, and Cancer Research UK. Founded in 2007, CCTC designs and conducts clinical trials and studies to improve outcomes for patients with cancer or those at risk of developing it. In 2011, CCTC began hosting the Cambridge Clinical Trials Unit - Cancer Theme (CCTU-CT).

CCTC has two divisions: Cancer Theme, which coordinates trial delivery, and Clinical Operations.