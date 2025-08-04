<?php

namespace CCTC\EmbellishFieldsModule;

use ExternalModules\AbstractExternalModule;

class EmbellishFieldsModule extends AbstractExternalModule {

    public function validateSettings($settings): ?string
    {
        if (array_key_exists("show-field-variable-name", $settings) && array_key_exists("show-field-element-type", $settings) && array_key_exists("show-field-validation-type", $settings) && array_key_exists("include-action-tags", $settings)) {
            if(empty($settings['show-field-variable-name']) && empty($settings['show-field-element-type']) && empty($settings['show-field-validation-type']) && empty($settings['include-action-tags'])) {
                return "Please ensure atleast one Embellish Fields External Module setting is configured.";
            }
        }
        if (array_key_exists("include-action-tags", $settings) && array_key_exists("action-tag-regex", $settings)) {
            If(!empty($settings['include-action-tags']) and empty($settings['action-tag-regex'])) {
                return "Please ensure the Action Tag Regex is configured when including action tags.";
            }
        }
        return null;
    }

    public function redcap_data_entry_form($project_id, $record, $instrument, $event_id, $group_id, $repeat_instance): void
    {
        if (empty($project_id)) return;

        global $Proj;

        echo "
            <script type='text/javascript'>
                function AddTag(field, actionTag) {
                    let ele = document.getElementById('label-' + field);
                    if(ele) {                        
                        const info = document.createElement('div');                    
                        info.innerHTML = '<small>' + actionTag + '</small>';                    
                        info.style.color = 'rgb(58, 113, 165)';
                        info.style.fontWeight = 'normal';
                        
                        ele.insertAdjacentElement('afterend', info);
                    }                    
                }
            </script>";

        //should include field name
        $includeFieldName = $this->getProjectSetting('show-field-variable-name') == "true";

        //should include field type
        $includeFieldElementType = $this->getProjectSetting('show-field-element-type') == "true";

        //should include field validation type
        $includeFieldValType = $this->getProjectSetting('show-field-validation-type') == "true";

        //should include endpoints
        $includeEndpoints = $this->getProjectSetting('include-action-tags') == "true";

        if (!$includeFieldName && !$includeFieldElementType && !$includeFieldValType && !$includeEndpoints) {
            echo "<script type='text/javascript'>
                    alert('Please ensure atleast one Embellish Fields External Module setting is configured.');
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
                if($trimmedRegex != null && trim($trimmedRegex) != "") {
                    preg_match_all("/$actionTagRegex/", $attrs['misc'], $matches);
                    if(!empty($matches[0])){
                        foreach ($matches[0] as $match) {
                            $infoToDisplay[$field][] = $match;
                        }
                    }
                }
            }
        }

        foreach ($infoToDisplay as $field => $info) {
            //display the items
            $infoAsString = implode(" | ", $info);
            echo "<script type='text/javascript'> AddTag('$field','$infoAsString') </script>";
        }
    }
}

