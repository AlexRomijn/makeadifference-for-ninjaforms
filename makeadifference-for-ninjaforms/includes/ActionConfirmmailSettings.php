<?php if ( ! defined( 'ABSPATH' ) ) exit;
/*
	All the settings fields for the custom action
*/
return array(

    /*
     * Type of protest: email protest or petition
     */

    'type_of_protest' => array(
        'name' => 'type_of_protest',
        'type' => 'select',
            'options' => array(
                array( 'label' => __( 'Protest email', 'nf-mad' ), 'value' => 'protest' ),
                array( 'label' => __( 'Petition', 'nf-mad' ), 'value' => 'petition' )
            ),
        'group' => 'primary',
        'label' => __( 'Type', 'nf-mad' ),
        'value' => 'protest',
    ),


    /*
     * Goal :: What is the goal of this email protest / petition
     */
    'Goal' => array(
        'name' => 'goal',
        'type' => 'textbox',
        'group' => 'primary',
        'label' => __( 'Goal', 'nf-mad' ),
        'placeholder' => __( '1000', 'nf-mad' ),
        'value' => '',
        'width' => 'one-half',
        'use_merge_tags' => FALSE,
    ),

    /*
     * SubmissionsCounted :: Total (confirmed) submissions
     */
    'SubmissionsCounted' => array(
        'name' => 'submissionscounted',
        'type' => 'textbox',
        'group' => 'primary',
        'label' => __( 'Submissions Counted', 'nf-mad' ),
        'placeholder' => __( '', 'nf-mad' ),
        'value' => '',
        'width' => 'one-half',
        'use_merge_tags' => FALSE,
    ),
	
    /*
     * SubmissionsCountedOffline :: Total (confirmed) submissions offline
     */
    'SubmissionsCountedOffline' => array(
        'name' => 'submissionscountedoffline',
        'type' => 'textbox',
        'group' => 'primary',
        'label' => __( 'Submissions Counted (Offline)', 'nf-mad' ),
        'placeholder' => __( '', 'nf-mad' ),
        'value' => '',
        'width' => 'one-half',
        'use_merge_tags' => FALSE,
    ),	
);