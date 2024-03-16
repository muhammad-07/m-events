<?php
// Register Custom Post Type
function create_m_event_post_type()
{
    register_post_type(
        'm-event',
        array(
            'labels' => array(
                'name' => __('M-Events'),
                'singular_name' => __('M-Event')
            ),
            'public' => true,
            'has_archive' => true,
            'supports' => array('title', 'editor', 'custom-fields')
        )
    );
}
add_action('init', 'create_m_event_post_type');



add_action('save_post', 'save_event_meta');
// Save Event Data
function save_event_meta($post_id)
{
    if (isset($_POST['event_start_date'])) {
        update_post_meta($post_id, 'event_start_date', sanitize_text_field($_POST['event_start_date']));
    }
    if (isset($_POST['event_end_date'])) {
        update_post_meta($post_id, 'event_end_date', sanitize_text_field($_POST['event_end_date']));
    }
    if (isset($_POST['is_recurring'])) {
        update_post_meta($post_id, 'is_recurring', $_POST['is_recurring']);
    }
    if (isset($_POST['interval'])) {
        update_post_meta($post_id, 'interval', $_POST['interval']);
    }
    if (isset($_POST['number_of_repetitions'])) {
        update_post_meta($post_id, 'number_of_repetitions', intval($_POST['number_of_repetitions']));
    }


    // Check if event is recurring
    if (isset($_POST['is_recurring']) && $_POST['is_recurring'] == 'yes') {
        // Retrieve recurring event options
        $number_of_repetitions = isset($_POST['number_of_repetitions']) ? intval($_POST['number_of_repetitions']) : 3;
        $interval = isset($_POST['interval']) ? sanitize_text_field($_POST['interval']) : 'monthly';

        // Check if the event is a parent event
        $parent_id = wp_get_post_parent_id($post_id);
        error_log($parent_id . 'is_recurring' . $_POST['is_recurring'] . 'I' . $_POST['interval']);

        remove_action('save_post', 'save_event_meta');
        // generate_repeating_events( $post_id, $number_of_repetitions, $interval );

        if ($parent_id) {
            // Update all child events in the series if requested by the user
            if (isset($_POST['update_series_option']) && $_POST['update_series_option'] == 'yes') {
                $child_events = get_children(array('post_parent' => $parent_id, 'post_type' => 'event'));
                foreach ($child_events as $child_event) {
                    $child_event_id = $child_event->ID;
                    update_event_details($child_event_id, $number_of_repetitions, $interval);
                }
            } else {
                // Otherwise, just update the current event
                update_event_details($post_id, $number_of_repetitions, $interval);
            }
        } else {
            // Generate repeating events
            generate_repeating_events($post_id, $number_of_repetitions, $interval);
        }
        add_action('save_post', 'save_event_meta');
    }
}



// Update Event Details

function update_event_details($event_id, $repetitions, $interval)
{

    // Get the original start and end dates from the parent event
    $parent_start_date = get_post_meta(wp_get_post_parent_id($event_id), 'event_start_date', true);
    $parent_end_date = get_post_meta(wp_get_post_parent_id($event_id), 'event_end_date', true);

    // Parse start and end dates
    $start_timestamp = strtotime($parent_start_date);
    $end_timestamp = strtotime($parent_end_date);

    // Calculate interval in seconds
    switch ($interval) {
        case 'daily':
            $interval_seconds = 24 * 60 * 60; // 1 day in seconds
            break;
        case 'weekly':
            $interval_seconds = 7 * 24 * 60 * 60; // 1 week in seconds
            break;
        case 'monthly':
            // Approximate month to 30 days
            $interval_seconds = 30 * 24 * 60 * 60; // 30 days in seconds
            break;
        default:
            $interval_seconds = 24 * 60 * 60; // Default to daily
            break;
    }

    // Update each event in the series
    // for ($i = 1; $i <= $repetitions; $i++) {
    //     // Calculate new event dates
    //     $new_start_date = date('Y-m-d', $start_timestamp + ($i - 1) * $interval_seconds);
    //     $new_end_date = date('Y-m-d', $end_timestamp + ($i - 1) * $interval_seconds);

    //     // Update event post meta
    //     update_post_meta($event_id, 'event_start_date', $new_start_date);
    //     update_post_meta($event_id, 'event_end_date', $new_end_date);

    //     // Update event post title (optional)
    //     $event_title = get_the_title(wp_get_post_parent_id($event_id)) . ' (Repetition ' . $i . ')';
    //     wp_update_post(array('ID' => $event_id, 'post_title' => $event_title));
    // }


    // Checking while update If we need to create recurring events
    create_inner_repeating_events();
    
}


function create_inner_repeating_events(){
    $number_of_repetitions = isset($_POST['number_of_repetitions']) ? intval($_POST['number_of_repetitions']) : 0;

    if ($number_of_repetitions > 0 && (isset($_POST['is_recurring']) && $_POST['is_recurring'] == 'yes')) { // TODO: posibly remove is_rec as funct call already aftr that
        
        $child_is_recurring = get_post_meta($event_id, 'is_recurring', true);
        if ($child_is_recurring == 'yes') {
            $child_event_start_date = get_post_meta($event_id, 'event_start_date', true);
            $child_event_end_date = get_post_meta($event_id, 'event_end_date', true);
            error_log("child_is_recurring if" . $event_id);
            $child_interval = get_post_meta($event_id, 'interval', true);
            if ($child_interval != $_POST['interval'] && $child_event_start_date != sanitize_text_field($_POST['event_start_date']) && $child_event_end_date != sanitize_text_field($_POST['child_event_end_date'])) {
                error_log("generate inner if" . $event_id);
                // generate recr evnt
                generate_repeating_events($event_id, $number_of_repetitions, $_POST['interval']);
            }
        } else {
            error_log("child_is_recurring else" . $event_id);
            generate_repeating_events($event_id, $number_of_repetitions, $_POST['interval']);
        }
    }
}

// Generate Recurring Events
function generate_repeating_events($event_id, $repetitions, $interval)
{
    $start_date = get_post_meta($event_id, 'event_start_date', true);
    $end_date = get_post_meta($event_id, 'event_end_date', true);

    // Parse start and end dates
    $start_timestamp = strtotime($start_date);
    $end_timestamp = strtotime($end_date);

    // Calculate interval in seconds
    switch ($interval) {
        case 'daily':
            $interval_seconds = 24 * 60 * 60; // 1 day in seconds
            break;
        case 'weekly':
            $interval_seconds = 7 * 24 * 60 * 60; // 1 week in seconds
            break;
        case 'monthly':
            // Approximate month to 30 days
            $interval_seconds = 30 * 24 * 60 * 60; // 30 days in seconds
            break;
        default:
            $interval_seconds = 24 * 60 * 60; // Default to daily
            break;
    }

    // Generate repeated events
    for ($i = 1; $i <= $repetitions; $i++) {
        // Calculate new event dates
        // $new_start_date = date('Y-m-d', $start_timestamp + ($i - 1) * $interval_seconds);
        // $new_end_date = date('Y-m-d', $end_timestamp + ($i - 1) * $interval_seconds);

        $calculateNextDates = calculateNextDates($start_timestamp, $end_timestamp, $interval);
        $new_start_date = $calculateNextDates['start_date'];
        $new_end_date = $calculateNextDates['end_date'];

        // Create new event post
        $new_event = array(
            'post_title' => get_the_title($event_id) . ' (Repetition ' . $i . ')',
            'post_content' => get_post_field('post_content', $event_id),
            'post_status' => get_post_field('post_status', $event_id),
            'post_type' => 'm-event',
            'post_author' => get_post_field('post_author', $event_id),
            'post_parent' => $event_id
        );

        // Insert new event post
        $new_event_id = wp_insert_post($new_event);
        error_log("new: " . $new_event_id);
        // Set event dates as post meta
        update_post_meta($new_event_id, 'event_start_date', $new_start_date);
        update_post_meta($new_event_id, 'event_end_date', $new_end_date);
        update_post_meta($new_event_id, 'is_recurring', 'No');


        delete_post_meta($new_event_id, 'number_of_repetitions ');
        delete_post_meta($new_event_id, 'interval');



        // $post_meta = get_post_meta($event_id);
        // foreach ($post_meta as $key => $values) {
        //     foreach ($values as $value) {
        //         // Exclude specific meta fields related to recurrence
        //         // if ($key !== '_is_recurring_event' && $key !== 'no_of_events' && $key !== 'select_recurring_type' && $key !== '_start_date_formatted') {

        //             if(!get_post_meta($new_event_id, $key))
        //             add_post_meta($new_event_id, $key, $value);
        //         // }
        //     }
        // }
        // Copy other post meta if needed
        // update_post_meta( $new_event_id, 'custom_meta_key', get_post_meta( $event_id, 'custom_meta_key', true ) );
    }
}
function calculateNextDates($event_start_date, $event_end_date, $selected_option = null)
{
	// global $event_start_date, $event_end_date;
	$start_date = \Carbon\Carbon::createFromTimestamp($event_start_date);
	$end_date = \Carbon\Carbon::createFromTimestamp($event_end_date);

	switch ($selected_option) {
		case 'daily':
			$start_date = $start_date->addDay();
			$end_date = $end_date->addDay();
			break;
		case 'weekly':
			$start_date = $start_date->addWeek();
			$end_date = $end_date->addWeek();
			break;
		case 'monthly':

			if ($start_date->day == 1 && $end_date->day >= 30) {
				// Modify the start date to the start of the next month
				$start_date = $start_date->addMonths(1)->startOfMonth();

				// Copy the start date and set it to the end of the next month
				$end_date = $start_date->copy()->endOfMonth();
			} else {
				// Increase the month by 1 for both start and end dates
				$start_date = $start_date->addMonthNoOverflow();
				$end_date = $end_date->addMonthNoOverflow();
			}
			break;
		default:

			break;
	}



	$start_date = $start_date->timestamp;
	$end_date = $end_date->timestamp;
	return array(
		'start_date' => $start_date,
		'end_date' => $end_date
	);
}

// Update Recurring Event
function update_recurring_event($event_id, $repetitions, $interval)
{
    // Get the original start and end dates from the parent event
    $parent_start_date = get_post_meta(wp_get_post_parent_id($event_id), 'event_start_date', true);
    $parent_end_date = get_post_meta(wp_get_post_parent_id($event_id), 'event_end_date', true);

    // Parse start and end dates
    $start_timestamp = strtotime($parent_start_date);
    $end_timestamp = strtotime($parent_end_date);

    // Calculate interval in seconds
    switch ($interval) {
        case 'daily':
            $interval_seconds = 24 * 60 * 60; // 1 day in seconds
            break;
        case 'weekly':
            $interval_seconds = 7 * 24 * 60 * 60; // 1 week in seconds
            break;
        case 'monthly':
            // Approximate month to 30 days
            $interval_seconds = 30 * 24 * 60 * 60; // 30 days in seconds
            break;
        default:
            $interval_seconds = 24 * 60 * 60; // Default to daily
            break;
    }

    // Update each event in the series
    for ($i = 1; $i <= $repetitions; $i++) {
        // Calculate new event dates
        $new_start_date = date('Y-m-d', $start_timestamp + ($i - 1) * $interval_seconds);
        $new_end_date = date('Y-m-d', $end_timestamp + ($i - 1) * $interval_seconds);

        // Update event post meta
        update_post_meta($event_id, 'event_start_date', $new_start_date);
        update_post_meta($event_id, 'event_end_date', $new_end_date);

        // Update event post title (optional)
        $event_title = get_the_title(wp_get_post_parent_id($event_id)) . ' (Repetition ' . $i . ')';
        wp_update_post(array('ID' => $event_id, 'post_title' => $event_title));
    }
}
