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

The module includes comprehensive **Cypress automated** tests using the **Cucumber/Gherkin framework**. To set up Cypress, refer to [Setup_Overview.md](https://github.com/CCTC-team/CCTC_REDCap_Docker/blob/redcap_val/Setup_Overview.md).

All automated test scripts are located in the `automated_tests` directory. The test suite automatically picks up the scripts from this folder. These scripts can also be used to manually test the external module. The directory contains:
- Fixture files
- User Requirement Specification (URS) documents
- Feature test scripts

**Step Definition Locations:**

Step definitions are organized across multiple locations in the `redcap_cypress` repo under `redcap_cypress/cypress/support/step_definitions/`:

- **Non-core feature step definitions** are in `redcap_cypress/cypress/support/step_definitions/noncore.js`
- **Shared EM step definitions** (used by more than one external module) are in `redcap_cypress/cypress/support/step_definitions/external_module.js`

#### GitHub Actions Workflow

The module ships with a CI workflow at [.github/workflows/cypress-tests.yml](.github/workflows/cypress-tests.yml) that runs this module's own Cypress specs end-to-end against a prebuilt all-in-one REDCap image, using a self-contained Cypress runner image. There is no 3-container compose, no host `npm ci`, and no cloning of the harness at runtime — both images are published ahead of time and pulled from GHCR.

**Triggers**
- `push` to `main` (ignoring doc-only changes: `**/*.md`, `LICENSE`, `.gitignore`, `docs/**`)
- Manual `workflow_dispatch`

**What it does** (`cypress-tests` job)
1. Checks out the Embellish Fields EM (this repo) into `embellish_fields_em/`.
2. Logs in to GHCR and pulls two prebuilt images: `redcap-aio` (REDCap + MariaDB + MailHog in one container via supervisord) and `cypress-runner-aio` (the suite with `rctf` + `redcap_rsvc` baked in).
3. Stages the EM under test — strips `.git`/`.github` so only the module payload remains.
4. Starts the AIO container (ports `8443`/`8025`, volume `cctc_mariadb_data`), bind-mounting **this commit's** EM over the image's `modules/embellish_fields_v1.1.2` so REDCap serves the code under test with no rebuild.
5. Waits for REDCap to come up (first boot initialises the DB).
6. Runs the runner image, which copies this module's `automated_tests` out of the container and runs only its `E.123.*` specs (excluding `*REDUNDANT*`), up to 3 attempts per spec, on Chromium. It reaches the DB/files over the mounted Docker socket and the UI over host networking.
7. Uploads the mochawesome reports (and, on failure, screenshots) as artifacts retained for 7 days.

**Follow-on jobs**
- `prune-artifacts` — deletes artifacts from older runs, keeping only the latest 2.
- `publish-report` — merges the run's mochawesome JSON into one combined HTML report and publishes it to GitHub Pages **per module version**: the report for this run lands at `/<EM_VERSION>/index.html` (e.g. `/v1.1.2/`) and the Pages root serves an index linking every published version, newest first. Previously published versions are preserved by restoring the cumulative site from the `pages-store` branch before the new version is added and the snapshot force-pushed back.

**Required repository secrets**
- `CCTC_TEAM_PAT` — PAT with `read:packages` for the private `redcap-aio` / `cypress-runner-aio` GHCR images.

**Version pins** (set as `env` at the top of the workflow)
- `REDCAP_VERSION` / `AIO_IMAGE` — the REDCap version under test (currently **17.5.6**) and the matching `redcap-aio:<version>` image.
- `RUNNER_IMAGE` — `cypress-runner-aio:latest`; its baked `cypress.env.json` `redcap_version` must match `REDCAP_VERSION`, since it copies that version's install SQL out of the AIO container.
- `EM_NAME` / `EM_VERSION` — `embellish_fields` / `v1.1.2`. `EM_MODULE` (`embellish_fields_v1.1.2`) is the directory REDCap discovers the module by and the runner uses to locate the specs. Bump `EM_VERSION`/`EM_MODULE` when releasing a new module version so the mount path and spec discovery stay aligned. The pins are the `||` defaults in the `env` block.

**Republishing an older version's report.** A manual run (Actions → Run workflow) takes four optional inputs that override the pins: `em_ref` (EM tag, branch or **full 40-char** commit SHA — actions/checkout reads a short SHA as a branch name), `em_version`, `redcap_version` and `runner_image`. Use them to rebuild a version's Pages report against the stack it was released on, e.g. v1.1.1 on REDCap 15.5.36: `em_ref=f38df7b5bb4d544c97681c6c978b2f0e924eb5c2`, `em_version=v1.1.1`, `redcap_version=15.5.36`, `runner_image=ghcr.io/cctc-team/redcap_cypress/cypress-runner-aio:redcap-15.5.36`. Only that version's folder is replaced; the others are kept.

---

## Who are we

The Cambridge Cancer Trials Centre (CCTC) is a collaboration between Cambridge University Hospitals NHS Foundation Trust, the University of Cambridge, and Cancer Research UK. Founded in 2007, CCTC designs and conducts clinical trials and studies to improve outcomes for patients with cancer or those at risk of developing it. In 2011, CCTC began hosting the Cambridge Clinical Trials Unit - Cancer Theme (CCTU-CT).

CCTC has two divisions: Cancer Theme, which coordinates trial delivery, and Clinical Operations.