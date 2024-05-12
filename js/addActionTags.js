$(document).ready(function () {
    if ($('#div_field_annotation').length === 0) {
        return;
    }

    $('body').on('dialogopen', function (event, ui) {
        let $popup = $(event.target);
        //let module = ExternalModules['PreventPastOrFutureDates'].ExternalModule;
        let module = ExternalModules['PreventDaysOfWeek'].ExternalModule;
        let futureDateTag = module.tt('futureDateTag');
        let pastDateTag = module.tt('pastDateTag');
        
        let saturdayTag = module.tt('saturdayTag');
        let sundayTag = module.tt('sundayTag');

        let mondayTag = module.tt('mondayTag');
        let tuesdayTag = module.tt('tuesdayTag');

        let wednesdayTag = module.tt('wednesdayTag');
        let thursdayTag = module.tt('thursdayTag');

        let fridayTag = module.tt('fridayTag');

        var result = $popup.find('tr td.nowrap:nth-child(2)').filter(function(index, element) { 
            var result = $(element).text() > futureDateTag;
            if (result) {
                return result;
            }
        });
        
        if (result.length < 1) return;

        let markerElement = $(result[0]).text();
        prependActionTag($popup, markerElement, futureDateTag, 
            'Prevents future dates from being entered into date field-—i.e., a text field with date validation applied. This and @PREVENT-PASTDATE are mutually exclusive, to enforce today use @TODAY');
        prependActionTag($popup, markerElement, pastDateTag, 
            'Prevents past dates from being entered into date field-—i.e., a text field with date validation applied. This and @PREVENT-FUTUREDATE are mutually exclusive, to enforce today use @TODAY');

        prependActionTag($popup, markerElement, saturdayTag, 
            'Prevents Saturdays from being entered into date field.');

        prependActionTag($popup, markerElement, sundayTag, 
            'Prevents Sundays from being entered into date field.');

        prependActionTag($popup, markerElement, mondayTag, 
            'Prevents Mondays from being entered into date field.');

        prependActionTag($popup, markerElement, tuesdayTag, 
            'Prevents Tuesdays from being entered into date field.');

        prependActionTag($popup, markerElement, wednesdayTag, 
            'Prevents Wednesdays from being entered into date field.');

        prependActionTag($popup, markerElement, thursdayTag, 
            'Prevents Thursdays from being entered into date field.');

        prependActionTag($popup, markerElement, fridayTag, 
            'Prevents Fridays from being entered into date field.');
    });

    var prependActionTag = function (container, markerElement, tagName, tagDescription) {
        // Aux function that checks if text matches the "@HIDECHOICE" string.
        var isDefaultLabelColumn = function () {
            // console.log($(this).text(), markerElement);
            return $(this).text() === markerElement;
        }

        // Getting markerElement row from action tags help table.
        var $default_action_tag = container.find('td').filter(isDefaultLabelColumn).parent();
        if ($default_action_tag.length !== 1) {
            return false;
        }

        // Create the help text
        var descr = $('<div></div>')
            .addClass('prevent-days-of-week-container')
            .html(tagDescription);

        // Creating a new action tag row.
        var $new_action_tag = $default_action_tag.clone();
        var $cols = $new_action_tag.children('td');
        var $button = $cols.find('button');

        // Column 1: updating button behavior.
        $button.attr('onclick', $button.attr('onclick').replace(markerElement, tagName));

        // Columns 2: updating action tag label.
        $cols.filter(isDefaultLabelColumn).text(tagName);

        // Column 3: updating action tag description.
        $cols.last().html(descr);

        // Placing new action tag.
        $new_action_tag.insertBefore($default_action_tag);
    }

});
