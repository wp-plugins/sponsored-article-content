<?php
/**
 * Plugin Name: Sponsored Article Content
 * Plugin URI: http://zeen101.com
 * Description: Add native ad functionality to your site by marking posts and articles as sponsored
 * Version: 1.02
 * Author: Zeen101 Team
 * Author URI: http://zeen101.com
 * License: GPL2
 */

$plugin = plugin_basename(__FILE__); 

// Add settings link on plugin page
function spa_settings_link($links) { 
  $settings_link = '<a href="options-general.php?page=sponsored-article/sponsored-article.php">Settings</a>'; 
  array_unshift($links, $settings_link); 
  return $links; 
}
add_filter( "plugin_action_links_$plugin" , 'spa_settings_link' );

add_action( 'admin_enqueue_scripts', 'spa_enqueue_color_picker' );
function spa_enqueue_color_picker( ) {
    wp_enqueue_style( 'wp-color-picker' );
    wp_enqueue_script( 'spa-script', plugins_url('script.js', __FILE__ ), array( 'wp-color-picker' ), false, true );
}

function spa_create_options_page() {
 
    add_options_page(
        'Sponsored Article',           
        'Sponsored Article',          
        'manage_options',          
        __FILE__,   
        'spa_settings_page'   
    );
 
} 
add_action('admin_menu', 'spa_create_options_page');

function spa_settings_page() {
 	?>
    <div class="wrap">

        <h2>Sponsored Article Options</h2>

      	<form method="post" action="options.php">
            <?php settings_fields( 'spa_options' ); ?>
            <?php do_settings_sections( 'spa_opt' ); ?>          
            <?php submit_button(); ?>
        </form>

    </div>

    <?php 
} 


add_action('admin_init', 'spa_admin_init');
function spa_admin_init() {
 
	register_setting(
        'spa_options',
        'spa_options',
        'spa_validate_options'
    );
     

    add_settings_section(
        'spa_general',        
        'General Options',                 
        '',
        'spa_opt'                           
    );

    add_settings_field(
        'spa_text',                  
        'Sponsored Text',                         
        'spa_text_input',  
        'spa_opt',                         
        'spa_general'       
    );

     add_settings_field(
        'spa_text_color',                  
        'Sponsored Text Color',                         
        'spa_text_color_input',  
        'spa_opt',                         
        'spa_general'       
    );

    add_settings_field(
        'spa_color',                  
        'Sponsored Background Color',                         
        'spa_color_input',  
        'spa_opt',                         
        'spa_general'       
    );

     add_settings_field(
        'spa_content_color',                  
        'Content Background Color',                         
        'spa_content_color_input',  
        'spa_opt',                         
        'spa_general'       
    );
} 

function spa_text_input() {

	$options = wp_parse_args( get_option( 'spa_options' ), array('spa_text' => 'Sponsored'));
	$text = $options['spa_text'];
	echo "<input id='spa_text' name='spa_options[spa_text]' value='$text'>";
}

function spa_text_color_input() {

    $options = wp_parse_args( get_option( 'spa_options' ), array('spa_text_color' => ''));
    $text_color = $options['spa_text_color'];
    echo "<input class='color-picker'  id='spa_text_color' name='spa_options[spa_text_color]' value='$text_color'>";
}

function spa_color_input() {

    $options = wp_parse_args( get_option( 'spa_options' ), array('spa_color' => ''));
    $color = $options['spa_color'];
    echo "<input class='color-picker' id='spa_color' name='spa_options[spa_color]' value='$color'>";
}

function spa_content_color_input() {

    $options = wp_parse_args( get_option( 'spa_options' ), array('spa_content_color' => ''));
    $content_color = $options['spa_content_color'];
    echo "<input class='color-picker' id='spa_content_color' name='spa_options[spa_content_color]' value='$content_color'>";

}

function spa_validate_options( $input ) {
    return $input;
}


// add meta box to post screen
add_action( 'add_meta_boxes', 'spa_meta_box_create' );
function spa_meta_box_create() {

	add_meta_box( 'spa-options', 'Sponsored Article', 'spa_options_function', 'post', 'side', 'high' );

    add_meta_box( 'spa-options', 'Sponsored Article', 'spa_options_function', 'article', 'side', 'high' );

}

function spa_options_function( $post ) {

	$is_ad = get_post_meta( $post->ID, '_spa_ad', true );

	?>

	<table class="form-table">
		<tr valign="top">
			<td>
				<input id="spa_ad" class="widefat" type="checkbox" name="spa_ad" <?php checked( 'on', $is_ad ); ?> /> <label for="spa_ad">This is a sponsored article</label>
			</td>
		</tr>
	</table>
	<?php 
}

add_action( 'save_post', 'spa_options_save_meta' );
function spa_options_save_meta( $post_id ) {

	if ( !empty( $_POST['spa_ad'] ) ) {
		update_post_meta( $post_id, '_spa_ad', $_POST['spa_ad'] );
	} else {
		update_post_meta( $post_id, '_spa_ad', 'off' );
	}

}


// add the visual indicator to the post title
add_filter( 'the_title', 'spa_ad_post_title');
function spa_ad_post_title ( $title ) {

    if ( !is_admin() ) { // so the markup won't display in the admin

        global $post;
        $is_ad = get_post_meta( $post->ID, '_spa_ad', true );

        $options = get_option( 'spa_options' );
        $text = $options['spa_text'];

        if ( $is_ad == 'on' && in_the_loop() ) {

            $title = '<span class="spa-indicator">' . $text . '</span> ' . $title;
        }
    }

  return $title;
}

// add a container to the content for styling purposes
add_filter( 'the_content', 'spa_ad_post_content');
function spa_ad_post_content ( $content ) {

	global $post;

	$is_ad = get_post_meta( $post->ID, '_spa_ad', true );

    if ( !is_page() && $is_ad == 'on' && in_the_loop() ) {
        $content = '<div class="spa-content"> ' . $content. '</div>';
    }
    
    return $content;
}

// a class to the body element for styling purposes
add_filter( 'body_class', 'spa_ad_body_classes');
function spa_ad_body_classes ( $classes ) {

	global $post;

	$is_ad = get_post_meta( $post->ID, '_spa_ad', true );

    if( !is_page() && $is_ad == 'on' ){

	   $classes[] = 'is-sponsored-article';

	}
  return $classes;
}

// add styles for the plugin to the head of the document
add_action( 'wp_head', 'spa_ad_styles' );
function spa_ad_styles() {

	$options = get_option( 'spa_options' );
	$color = $options['spa_color'];
    $text_color = $options['spa_text_color'];
    $content_color = $options['spa_content_color'];

	?>
	<style type="text/css" media="screen">
		.spa-indicator { color: <?php echo $text_color; ?>; font-size: .8em; background: <?php echo $color; ?>; border-radius: 2px; padding: 0 2px; display: inline-block; z-index: 99;}
        .spa-content {
            background: <?php echo $content_color; ?>; padding: 10px;
        }
	</style>
	<?php 
}