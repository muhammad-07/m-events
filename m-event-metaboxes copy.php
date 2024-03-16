<?php
// Add metaboxes for event details
function event_details_metabox() {
    add_meta_box(
        'event_details_metabox',
        __( 'Event Details', 'event-plugin' ),
        'event_details_metabox_callback',
        'm-event',
        'normal',
        'high'
    );
}
add_action( 'add_meta_boxes', 'event_details_metabox' );




// Metabox Callback
function event_details_metabox_callback( $post ) {
    // Retrieve event start and end dates
    $event_start_date = get_post_meta( $post->ID, 'event_start_date', true );
    $event_end_date = get_post_meta( $post->ID, 'event_end_date', true );

    // Retrieve recurring event options
    $is_recurring = get_post_meta( $post->ID, 'is_recurring', true );
    $interval = get_post_meta( $post->ID, 'interval', true );
    $number_of_repetitions = get_post_meta( $post->ID, 'number_of_repetitions', true );

    // Check if event is part of a series
    // $parent_id = wp_get_post_parent_id( $post->ID );
    // if ( $parent_id ) {
        ?>
        <label for="update_series_option"><?php _e( 'Update title and description:', 'event-plugin' ); ?></label>
        <input type="checkbox" id="update_series_option" name="update_series_option" value="yes"><br>
        
        <?php
    // }

    // Add fields for recurring event options
    ?>

<label for="event_start_date"><?php _e( 'Start Date:', 'event-plugin' ); ?></label>
    <input type="date" id="event_start_date" name="event_start_date" value="<?php echo esc_attr( $event_start_date ); ?>"><br>

    <label for="event_end_date"><?php _e( 'End Date:', 'event-plugin' ); ?></label>
    <input type="date" id="event_end_date" name="event_end_date" value="<?php echo esc_attr( $event_end_date ); ?>"><br>

    <label for="is_recurring"><?php _e( 'Is Recurring Event:', 'event-plugin' ); ?></label>
    <input type="checkbox" id="is_recurring" name="is_recurring" value="yes" <?php checked( $is_recurring, 'yes' ); ?>><br>

    <div id="recurring_options" style="<?php echo ( $is_recurring == 'yes' ) ? 'display: block;' : 'display: none;'; ?>">
        <label for="interval"><?php _e( 'Interval:', 'event-plugin' ); ?></label>
        <select id="interval" name="interval">
            <option value="daily" <?php selected( $interval, 'daily' ); ?>><?php _e( 'Daily', 'event-plugin' ); ?></option>
            <option value="weekly" <?php selected( $interval, 'weekly' ); ?>><?php _e( 'Weekly', 'event-plugin' ); ?></option>
            <option value="monthly" <?php selected( $interval, 'monthly' ); ?>><?php _e( 'Monthly', 'event-plugin' ); ?></option>
        </select><br>

        <label for="number_of_repetitions"><?php _e( 'Number of Repetitions:', 'event-plugin' ); ?></label>
        <input type="number" id="number_of_repetitions" name="number_of_repetitions" value="<?php echo esc_attr( $number_of_repetitions ); ?>" ><br>
    </div>
    
    <script>
        // Show/hide recurring options based on is_recurring checkbox
        jQuery(document).ready(function($) {
            $('#is_recurring').change(function() {
                $('#recurring_options').toggle();
            });
        });
    </script>
    <?php
}

