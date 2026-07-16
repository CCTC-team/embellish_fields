<?php

namespace CCTC\EmbellishFieldsModule;

use ExternalModules\AbstractExternalModule;

class EmbellishFieldsModule extends AbstractExternalModule {

    public function validateSettings($settings): ?string
    {
        if (array_key_exists("show-field-variable-name", $settings) && array_key_exists("show-field-element-type", $settings) && array_key_exists("show-field-validation-type", $settings) && array_key_exists("include-action-tags", $settings)) {
            if(empty($settings['show-field-variable-name']) && empty($settings['show-field-element-type']) && empty($settings['show-field-validation-type']) && empty($settings['include-action-tags'])) {
                return "Please ensure at least one Embellish Fields External Module setting is configured.";
            }
        }
        if (array_key_exists("include-action-tags", $settings) && array_key_exists("action-tag-regex", $settings)) {
            if(!empty($settings['include-action-tags']) && empty($settings['action-tag-regex'])) {
                return "Please ensure the Action Tag Regex is configured when including action tags.";
            }
        }
        return null;
    }

    public function redcap_data_entry_form($project_id, $record, $instrument, $event_id, $group_id, $repeat_instance): void
    {
        if (empty($project_id)) return;

        global $Proj;

        // Include external CSS and JavaScript files
        echo '<link rel="stylesheet" href="' . $this->getUrl('css/embellish.css') . '">';
        echo '<script src="' . $this->getUrl('js/embellish.js') . '"></script>';

        // Get settings using boolean cast for reliable checkbox handling
        $includeFieldName = (bool)$this->getProjectSetting('show-field-variable-name');
        $includeFieldElementType = (bool)$this->getProjectSetting('show-field-element-type');
        $includeFieldValType = (bool)$this->getProjectSetting('show-field-validation-type');
        $includeEndpoints = (bool)$this->getProjectSetting('include-action-tags');

        if (!$includeFieldName && !$includeFieldElementType && !$includeFieldValType && !$includeEndpoints) {
            echo "<script type='text/javascript'>
                    alert('Please ensure at least one Embellish Fields External Module setting is configured.');
                </script>";
            return;
        }

        if($includeEndpoints) {
            $actionTagRegex = $this->getProjectSetting('action-tag-regex');
        }

        $infoToDisplay = [];

        foreach ($Proj->metadata as $field => $attrs) {
            //field name
            if($includeFieldName) {
                $infoToDisplay[$field][] = $field;
            }

            //field element type
            if($includeFieldElementType) {
                $infoToDisplay[$field][] = $attrs['element_type'];
            }

            //field validation type
            if($includeFieldValType && isset($attrs['element_validation_type'])) {
                $infoToDisplay[$field][] = $attrs['element_validation_type'];
            }

            //endpoints
            if($includeEndpoints) {
                $trimmedRegex = trim($actionTagRegex);

                //only include if the action regex is present and valid
                if(!empty($trimmedRegex)) {
                    // Security fix: Validate regex pattern before use to prevent errors
                    if (@preg_match("/" . $trimmedRegex . "/", '') === false) {
                        // Invalid regex pattern - log error and skip
                        $this->log('Invalid regex pattern configured', [
                            'pattern' => $trimmedRegex,
                            'project_id' => $project_id,
                            'field' => $field
                        ]);
                    } else {
                        preg_match_all("/" . $trimmedRegex . "/", $attrs['misc'], $matches);
                        if(!empty($matches[0])){
                            foreach ($matches[0] as $match) {
                                // Escape match content for safety
                                $infoToDisplay[$field][] = htmlspecialchars($match, ENT_QUOTES, 'UTF-8');
                            }
                        }
                    }
                }
            }
        }

        foreach ($infoToDisplay as $field => $info) {
            //display the items
            $infoAsString = implode(" | ", $info);
            // Security fix: Use json_encode to prevent XSS attacks from field names or action tags
            $safeField = json_encode($field);
            $safeInfo = json_encode($infoAsString);
            echo "<script type='text/javascript'> AddTag($safeField, $safeInfo) </script>";
        }
    }

    // ---------------------------------------------------------------------
    // Configuration audit log
    //
    // REDCap core already logs *which* keys changed (and who/when) to the
    // project Logging page. What core cannot do is record old->new VALUES on
    // this module's own "View logging" page. This generic, config-driven hook
    // fills that gap: it diffs each saved setting against a snapshot kept under
    // a reserved key and writes one entry per changed setting via $this->log().
    // The block is identical across all CCTC EM repos (config-driven, no
    // per-repo edits) and handles both project and system scopes.
    // ---------------------------------------------------------------------
    public function redcap_module_save_configuration($project_id): void
    {
        $this->auditConfigurationChange($project_id);
    }

    private function auditConfigurationChange($project_id): void
    {
        $config   = $this->getConfig();
        $isSystem = empty($project_id);
        $scope    = $isSystem ? 'system' : 'project';
        $keys     = $this->collectSettingKeys($config[$scope . '-settings'] ?? []);
        if (empty($keys)) return;

        $snapshotKey = 'audit-snapshot-' . $scope;
        $read  = fn($k)     => $isSystem ? $this->getSystemSetting($k)    : $this->getProjectSetting($k);
        $write = fn($k, $v) => $isSystem ? $this->setSystemSetting($k, $v) : $this->setProjectSetting($k, $v);

        $new = [];
        foreach ($keys as $k) $new[$k] = $this->normaliseSetting($read($k));

        $rawOld = $read($snapshotKey);
        $old = is_string($rawOld) ? json_decode($rawOld, true) : null;
        // First save has no prior snapshot: treat the baseline as empty so the
        // initial configuration's real values are still logged ((empty) -> value),
        // while settings left blank stay '' vs '' and produce no noise.
        if (!is_array($old)) $old = [];

        $changed = false;
        foreach ($keys as $k) {
            $before = $this->normaliseSetting($old[$k] ?? null);
            $after  = $new[$k];
            if ($before !== $after) {
                $changed = true;
                $this->log("Configuration changed ($scope)", [
                    'project_id' => $project_id,
                    'setting'    => $k,
                    'old_value'  => $before === '' ? '(empty)' : $before,
                    'new_value'  => $after  === '' ? '(empty)' : $after,
                ]);
            }
        }
        if ($changed) $write($snapshotKey, json_encode($new));
    }

    private function collectSettingKeys(array $settings): array
    {
        $keys = [];
        foreach ($settings as $s) {
            if (!isset($s['key'])) continue;
            if (($s['type'] ?? '') === 'descriptive') continue;
            $keys[] = $s['key'];
        }
        return $keys;
    }

    private function normaliseSetting($v): string
    {
        if ($v === null || $v === false) return '';
        if ($v === true) return '1';
        if (is_array($v)) return json_encode($v);
        return trim((string) $v);
    }
}

