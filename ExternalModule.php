<?php

namespace PreventDaysOfWeek\ExternalModule;

use ExternalModules\AbstractExternalModule;

abstract class Page
{
    const DATA_ENTRY = 'DataEntry/index.php';
    const ONLINE_DESIGNER = 'Design/online_designer.php';
    const SURVEY = 'surveys/index.php';
    const SURVEY_THEME = 'Surveys/theme_view.php';
}

abstract class ResourceType
{
    const CSS = 'css';
    const HTML = 'html';
    const JS = 'js';
}

abstract class Validate
{
    static $date_validations = [
        'date_dmy',
        'date_mdy',
        'date_ymd',
        'datetime_dmy',
        'datetime_mdy',
        'datetime_ymd',
        'datetime_seconds_dmy',
        'datetime_seconds_mdy',
        'datetime_seconds_ymd',
    ];

    // TODO: Pass in global variables and provide init function
    static function hasProjectID(): bool
    {
        return isset($project_id);
    }

    // TODO: Pass in global variables and provide init function
    static function hasRecordID(): bool
    {
        return isset($_GET['id']);
    }

    static function pageIs(string $page): bool
    {
        return PAGE == $page;
    }

    static function pageIsIn(array $pages): bool
    {
        return in_array(PAGE, $pages);
    }
}

class ExternalModule extends AbstractExternalModule
{
    public $futureDateTag = "@PREVENT-FUTUREDATE";
    public $pastDateTag = "@PREVENT-PASTDATE";

    public $saturdayTag = "@PREVENT-SATURDAY";
    public $sundayTag = "@PREVENT-SUNDAY";
    public $mondayTag = "@PREVENT-MONDAY";
    public $tuesdayTag = "@PREVENT-TUESDAY";
    public $wednesdayTag = "@PREVENT-WEDNESDAY";
    public $thursdayTag = "@PREVENT-THURSDAY";
    public $fridayTag = "@PREVENT-FRIDAY";

    function containsFutureDateTag(?string $tags): bool
    {
        return (isset($tags)) ? in_array($this->futureDateTag, explode(' ', $tags)) : false;
    }

    function containsPastDateTag(?string $tags): bool
    {
        return (isset($tags)) ? in_array($this->pastDateTag, explode(' ', $tags)) : false;
    }

    function containsSaturdayTag(?string $tags): bool
    {
        //echo "saturday tag is: " . $this->saturdayTag;
        //echo "<br>";
        return (isset($tags)) ? in_array($this->saturdayTag, explode(' ', $tags)) : false;
    }

    function containsSundayTag(?string $tags): bool
    {
        return (isset($tags)) ? in_array($this->sundayTag, explode(' ', $tags)) : false;
    }

    function containsMondayTag(?string $tags): bool
    {
        return (isset($tags)) ? in_array($this->mondayTag, explode(' ', $tags)) : false;
    }

    function containsTuesdayTag(?string $tags): bool
    {
        return (isset($tags)) ? in_array($this->tuesdayTag, explode(' ', $tags)) : false;
    }

        function containsWednesdayTag(?string $tags): bool
    {
        return (isset($tags)) ? in_array($this->wednesdayTag, explode(' ', $tags)) : false;
    }

        function containsThursdayTag(?string $tags): bool
    {
        return (isset($tags)) ? in_array($this->thursdayTag, explode(' ', $tags)) : false;
    }

        function containsFridayTag(?string $tags): bool
    {
        return (isset($tags)) ? in_array($this->fridayTag, explode(' ', $tags)) : false;
    }

    // Given $Proj->metadata[$field_name] return whether the field 
    // is a text field and has date validation applied
    function isDateTypeField(array $field): bool
    {
        $isTextField = $field['element_type'] == 'text';
        $hasDateValidation = in_array($field['element_validation_type'], Validate::$date_validations);
        return $isTextField && $hasDateValidation;
    }

    function includeSource(string $resourceType, string $path)
    {
        switch ($resourceType) {
            case ResourceType::CSS:
                echo '<link href="' . $this->getUrl($path) . '" rel="stylesheet">';
                break;
            case ResourceType::HTML:
                include($path);
                break;
            case ResourceType::JS:
                echo '<script src="' . $this->getUrl($path) . '"></script>';
                break;
            default:
                break;
        }
    }

    /*
     * Note: min and max validations set on the field do not prevent entering past or future dates.
     * $element_validation_min = $field['element_validation_min'];
     * $element_validation_max = $field['element_validation_max'];
    **/
    function redcap_every_page_top($project_id)
    {


        if (Validate::pageIs(Page::ONLINE_DESIGNER) && $project_id) {
            $this->initializeJavascriptModuleObject();
            $this->tt_addToJavascriptModuleObject('futureDateTag', $this->futureDateTag);
            $this->tt_addToJavascriptModuleObject('pastDateTag', $this->pastDateTag);

            $this->tt_addToJavascriptModuleObject('saturdayTag', $this->saturdayTag);
            $this->tt_addToJavascriptModuleObject('sundayTag', $this->sundayTag);
            $this->tt_addToJavascriptModuleObject('mondayTag', $this->mondayTag);
            $this->tt_addToJavascriptModuleObject('tuesdayTag', $this->tuesdayTag);
            $this->tt_addToJavascriptModuleObject('wednesdayTag', $this->wednesdayTag);
            $this->tt_addToJavascriptModuleObject('thursdayTag', $this->thursdayTag);
            $this->tt_addToJavascriptModuleObject('fridayTag', $this->fridayTag);



            $this->includeSource(ResourceType::JS, 'js/addActionTags.js');
        } else if (Validate::pageIsIn(array(Page::DATA_ENTRY, Page::SURVEY, Page::SURVEY_THEME)) && isset($_GET['id'])) {
            global $Proj;
            $instrument = $_GET['page'];
            $preventFutureDateFields = [];
            $preventPastDateFields = [];
            $preventSaturdayFields = [];
            $preventSundayFields = [];
            $preventMondayFields = [];
            $preventTuesdayFields = [];
            $preventWednesdayFields = [];
            $preventThursdayFields = [];
            $preventFridayFields = [];

            $preventDaysFields = [];

            $preventDaysFieldsDays = [];

            //echo "Hello world, scheduler!";

            // Iterate through all fields and search for date fields with @PREVENT-FUTUREDATE or @PREVENT-PASTDATE
            // and add them to an array to pass to JS to apply date restrictions. If both tags are added then no
            // restrictions will be applied.
            foreach (array_keys($Proj->forms[$instrument]['fields']) as $field_name) {
                $field = $Proj->metadata[$field_name];
                if ($this->isDateTypeField($field)) {
                    $action_tags = $field['misc'];

                    $daysActionTagFlag = false;
                    $daysActionTag = "";

                    if ($this->containsFutureDateTag($action_tags) && !$this->containsPastDateTag($action_tags)) {
                        array_push($preventFutureDateFields, $field_name);
                    }

                    if ($this->containsPastDateTag($action_tags) && !$this->containsFutureDateTag($action_tags)) {
                        array_push($preventPastDateFields, $field_name);
                    }

                    if ($this->containsSundayTag($action_tags)) {
                        $daysActionTagFlag = true;
                        $daysActionTag = $daysActionTag . "0";
                        array_push($preventSundayFields, $field_name);
                    } 

                    if ($this->containsMondayTag($action_tags)) {
                        $daysActionTagFlag = true;
                        $daysActionTag = $daysActionTag . "1";
                        array_push($preventMondayFields, $field_name);
                    }   

                    if ($this->containsTuesdayTag($action_tags)) {
                        $daysActionTagFlag = true;
                        $daysActionTag = $daysActionTag . "2";
                        array_push($preventTuesdayFields, $field_name);
                    }  

                    if ($this->containsWednesdayTag($action_tags)) {
                        $daysActionTagFlag = true;
                        $daysActionTag = $daysActionTag . "3";
                        array_push($preventWednesdayFields, $field_name);
                    }     

                    if ($this->containsThursdayTag($action_tags)) {
                        $daysActionTagFlag = true;
                        $daysActionTag = $daysActionTag . "4";
                        array_push($preventThursdayFields, $field_name);
                    }      

                    if ($this->containsFridayTag($action_tags)) {
                        $daysActionTagFlag = true;
                        $daysActionTag = $daysActionTag . "5";
                        array_push($preventFridayFields, $field_name);
                    }   

                    if ($this->containsSaturdayTag($action_tags)) {
                        $daysActionTagFlag = true;
                        $daysActionTag = $daysActionTag . "6";
                        array_push($preventSaturdayFields, $field_name);
                    }   

                    if($daysActionTagFlag === true){
                        $preventDaysFieldsDays[] = $$field_name;
                        $preventDaysFieldsDays[] = $daysActionTag;
                    }    
                }
            }


/*
            echo "<pre>";
            print_r($preventSaturdayFields);
            echo "</pre>";

            echo "<pre>";
            print_r($preventPastDateFields);
            echo "</pre>";
*/
            $this->initializeJavascriptModuleObject();
            $this->tt_addToJavascriptModuleObject('preventFutureDateFields', json_encode($preventFutureDateFields));
            $this->tt_addToJavascriptModuleObject('preventPastDateFields', json_encode($preventPastDateFields));

            $this->tt_addToJavascriptModuleObject('preventSaturdayFields', json_encode($preventSaturdayFields));
            $this->tt_addToJavascriptModuleObject('preventSundayFields', json_encode($preventSundayFields));
            $this->tt_addToJavascriptModuleObject('preventMondayFields', json_encode($preventMondayFields));
            $this->tt_addToJavascriptModuleObject('preventTuesdayFields', json_encode($preventTuesdayFields));
            $this->tt_addToJavascriptModuleObject('preventWednesdayFields', json_encode($preventWednesdayFields));
            $this->tt_addToJavascriptModuleObject('preventThursdayFields', json_encode($preventThursdayFields));
            $this->tt_addToJavascriptModuleObject('preventFridayFields', json_encode($preventFridayFields));
            $this->tt_addToJavascriptModuleObject('preventDaysFieldsDays', json_encode($preventDaysFieldsDays));
            //$this->includeSource(ResourceType::JS, 'js/preventPastOrFutureDates.js');
            $this->includeSource(ResourceType::JS, 'js/preventDaysOfWeek.js');
        }
    }
}
