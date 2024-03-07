<?php
/*
Plugin Name: Team Members
Description: A plugin for Team Members post type.
Version: 1.0
Author: Raiyan Noory Rady
*/

function otm_scripts(){
    wp_enqueue_style('bootstrap-css', '//cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css');
    wp_enqueue_style('custom-style', plugin_dir_url(__FILE__) . 'style.css');
    wp_enqueue_style('grid-style', plugin_dir_url(__FILE__) . 'grid.css');
    
}
// Register Team Member Post Type
function team_member_post_type() {
    $labels = array(
        'name'               => 'Team Members',
        'singular_name'      => 'Team Member',
        'menu_name'          => 'Team Members',
        'name_admin_bar'     => 'Team Member',
        'add_new'            => 'Add New',
        'add_new_item'       => 'Add New Team Member',
        'new_item'           => 'New Team Member',
        'edit_item'          => 'Edit Team Member',
        'view_item'          => 'View Team Member',
        'all_items'          => 'All Team Members',
        'search_items'       => 'Search Team Members',
        'parent_item_colon'  => 'Parent Team Members:',
        'not_found'          => 'No team members found.',
        'not_found_in_trash' => 'No team members found in Trash.'
    );

    $args = array(
        'labels'             => $labels,
        'public'             => true,
        'publicly_queryable' => true,
        'show_ui'            => true,
        'show_in_menu'       => true,
        'query_var'          => true,
        'rewrite'            => array( 'slug' => 'team-member' ),
        'capability_type'    => 'post',
        'has_archive'        => true,
        'hierarchical'       => false,
        'menu_position'      => null,
        'supports'           => array( 'title', 'editor', 'thumbnail' ),
    );

    register_post_type( 'team_member', $args );
}

// Register hierarchical taxonomy 'Member type' for 'Team Member' post type
function team_member_taxonomy() {
    $labels = array(
        'name'              => 'Member Types',
        'singular_name'     => 'Member Type',
        'search_items'      => 'Search Member Types',
        'all_items'         => 'All Member Types',
        'parent_item'       => 'Parent Member Type',
        'parent_item_colon' => 'Parent Member Type:',
        'edit_item'         => 'Edit Member Type',
        'update_item'       => 'Update Member Type',
        'add_new_item'      => 'Add New Member Type',
        'new_item_name'     => 'New Member Type',
        'menu_name'         => 'Member Types',
    );

    $args = array(
        'hierarchical'      => true,
        'labels'            => $labels,
        'show_ui'           => true,
        'show_admin_column' => true,
        'query_var'         => true,
        'rewrite'           => array( 'slug' => 'member-type' ),
    );

    register_taxonomy( 'member_type', 'team_member', $args );
}

// Add custom fields for Position only
function team_member_custom_fields() {
    add_meta_box(
        'team_member_fields',
        'Team Member Details',
        'team_member_fields_callback',
        'team_member',
        'normal',
        'high'
    );
}

function team_member_fields_callback( $post ) {
    $position = get_post_meta( $post->ID, '_team_member_position', true );

    ?>
    <p>
        <label for="team_member_position">Position:</label>
        <input type="text" id="team_member_position" name="team_member_position" value="<?php echo esc_attr( $position ); ?>">
    </p>
    <?php
}

function save_team_member_fields( $post_id ) {
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
        return;
    }

    if ( isset( $_POST['team_member_position'] ) ) {
        update_post_meta( $post_id, '_team_member_position', sanitize_text_field( $_POST['team_member_position'] ) );
    }
}

// Change the placeholder text for the title input field
function team_member_change_title_placeholder( $title ) {
    $screen = get_current_screen();

    if ( $screen->post_type == 'team_member' ) {
        $title = 'Enter Member Name';
    }

    return $title;
}

// Customize the text for the featured image section
function team_member_change_featured_image_text( $content ) {
    global $post_type;

    if ( 'team_member' == $post_type ) {
        $content = str_replace( 'Set featured image', 'Set Team Member Picture', $content );
    }

    return $content;
}

// Shortcode function to display team members
// Shortcode function to display team members
function team_members_shortcode() {
    $args = array(
        'post_type'      => 'team_member',
        'posts_per_page' => -1,
        'order'          => 'ASC',
    );

    $team_members = new WP_Query( $args );

    if ( $team_members->have_posts() ) {
        $output = '<div class="row">';

        while ( $team_members->have_posts() ) {
            $team_members->the_post();
            $output .= '<div class="col-md-6 col-lg-3 mt-5 text-center">';
            
            
            // Display featured image
            if (has_post_thumbnail()) {
                $output .= '<div class="tm-img">' . get_the_post_thumbnail(get_the_ID(), 'thumbnail') . '</div>';
            }
            $output .= '<strong class="mt-5">' . get_the_title() . '</strong><br>';
            // Display position
            $position = get_post_meta(get_the_ID(), '_team_member_position', true);
            $output .= '' . esc_html($position) . '</div>';
        }

        $output .= '</div>';
    } else {
        $output = 'No team members found.';
    }

    wp_reset_postdata();

    return $output;
}

//enqueue stylesheet
add_action('wp_enqueue_scripts','otm_scripts');
// Hook into the 'init' action to register the post type and taxonomy
add_action( 'init', 'team_member_post_type' );
add_action( 'init', 'team_member_taxonomy' );

// Hook into the 'add_meta_boxes' action to add custom fields
add_action( 'add_meta_boxes', 'team_member_custom_fields' );

// Hook into the 'save_post' action to save custom fields
add_action( 'save_post', 'save_team_member_fields' );

// Hook into the 'enter_title_here' filter to change the placeholder text
add_filter( 'enter_title_here', 'team_member_change_title_placeholder' );

// Hook into the 'admin_post_thumbnail_html' filter to change the text for the featured image
add_filter( 'admin_post_thumbnail_html', 'team_member_change_featured_image_text' );

// Register the shortcode
add_shortcode( 'team_members', 'team_members_shortcode' );
