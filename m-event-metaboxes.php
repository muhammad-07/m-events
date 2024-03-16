<?php
require_once('vendor/CMB2/init.php');

function enqueue_custom_scripts()
{
	wp_enqueue_script('reg_meta_box', plugin_dir_url(__FILE__) . 'reg_meta_box.js', array('jquery'), null, true);
	wp_localize_script('reg_meta_box', 'lys_admin', array('ajax_url' => admin_url('admin-ajax.php')));
}
add_action('init', 'enqueue_custom_scripts');

function register_campaign_speakers_meta_box()
{
	add_meta_box(
		'campaign_speakers_taxonomy_meta_box',
		__('Speakers', 'artcloud-events-block'),
		'render_campaign_speakers_meta_box',
		'm-event',
		'side'
	);
}

function render_campaign_speakers_meta_box($post, $renderSpeakerAddForm = true)
{
	$campaign_speaker_terms = wp_get_post_terms($post->ID, 'campaign_speakers');
	$all_terms = wp_get_post_terms($post->ID, 'campaign_speakers', array('hide_empty' => false));
	echo '<input type="text" id="search_event_speakers" name="search_event_speakers" data-id="' . $post->ID . '" value="" /><hr>';
	echo '<ul id="event_speakers_suggestions"></ul>';
	echo '<div id="dynamic-speaker-content">';
	foreach ($all_terms as $term) {
		$checked      = in_array($term, $campaign_speaker_terms) ? 'checked' : '';
		$term_meta    = unserialize(get_term_meta($term->term_id, 'campaign_speakers_data', true));
		$phone_number = $term_meta['speakers_phonenumber'];
		$email        = $term_meta['speakers_email'];
		$phone_checked      = $term_meta['phone_checked'] == 1 ? 'checked' : '';
		$email_checked      = $term_meta['email_checked'] == 1 ? 'checked' : '';
		echo '<input type="checkbox" name="campaign_speaker_terms[]" value="' . $term->term_id . '" ' . $checked . '>'
			. esc_html($term->name);
		echo '<br>';
		
		
	}
	echo '</div>';
	if ($renderSpeakerAddForm) {
		echo '<div id="static-speaker-content">';
		echo '<a href="#" id="add-new-speaker" class="button button-primary">' . __('Add New Speaker', 'artcloud-events-block') . '</a>';

		echo '<div id="new-speaker-form" style="display:none;">';
		echo '<h4>' . __('Add New Speaker', 'artcloud-events-block') . '</h4>';
		echo '<input type="text" id="new-speaker-name" name="new_speaker_name" placeholder="' . __('Name', 'artcloud-events-block') . '"/>';
		echo '<br>';
		echo '<input type="text" id="new-speaker-phone" name="new_speaker_phone" placeholder="' . __('Phone', 'artcloud-events-block') . '"/>';
		echo '<br>';
		echo '<input type="text" id="new-speaker-email" name="new_speaker_email" placeholder="' . __('Email', 'artcloud-events-block') . '"/>';
		echo '<br>';
		echo '<a href="#" id="save-new-speaker" class="button">' . __('Save Speaker', 'artcloud-events-block') . '</a>';
		echo '</div>';
	
		echo '</div>';
	}

	echo '<hr>';



    $show_email_for_speakers = get_post_meta($post->ID, '_show_email_for_speakers', true);
    $show_phone_for_speakers = get_post_meta($post->ID, '_show_phone_for_speakers', true);
	// checked($show_email_for_speakers, 'on', true);

    echo '<input type="checkbox" id="show_email_for_speakers" name="show_email_for_speakers" value="1" ' . checked($show_email_for_speakers, 'on') . '> ' . __('Show Speaker\'s Email', 'artcloud-events-block') . '<br>';
    echo '<input type="checkbox" id="show_phone_for_speakers" name="show_phone_for_speakers" value="1" ' .  checked($show_phone_for_speakers, 'on') . '> ' . __('Show Speaker\'s Phone', 'artcloud-events-block') . '<br>';
}



function add_event_custom_fields() {
    $cmb = new_cmb2_box( array(
        'id' => 'event_metabox',
        'title' => __( 'Event Details', 'cmb2' ),
        'object_types' => array( 'm-event' ), // Post type
    ) );
    $cmb->add_field( array(
        'name' => __( 'Start Date/Time', 'cmb2' ),
        'id'   => 'event_start_datetime',
        'type' => 'text_datetime_timestamp',
    ) );
    $cmb->add_field( array(
        'name' => __( 'End Date/Time', 'cmb2' ),
        'id'   => 'event_end_datetime',
        'type' => 'text_datetime_timestamp',
    ) );
    $cmb->add_field( array(
        'name' => __( 'Recurring Event?', 'cmb2' ),
        'id'   => 'event_recurring',
        'type' => 'checkbox',
    ) );
    $cmb->add_field( array(
        'name' => __( 'Recurrence', 'cmb2' ),
        'id'   => 'event_recurrence',
        'type' => 'select',
        'options' => array(
            'daily' => __( 'Daily', 'cmb2' ),
            'weekly' => __( 'Weekly', 'cmb2' ),
            'monthly' => __( 'Monthly', 'cmb2' ),
        ),
    ) );
    $cmb->add_field( array(
        'name' => __( 'Number of Events', 'cmb2' ),
        'id'   => 'event_count',
        'type' => 'text_small',
    ) );
}
add_action( 'cmb2_init', 'add_event_custom_fields' );
