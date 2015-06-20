<?php
/*
Plugin Name: WP Tour
Version: 0.0.1
Description: Create a WordPress Tour with handy dandy pop ups
Author: Jurgen de Vries
Author URI: http://www.jurgendevries.nl
Plugin URI: http://www.jurgendevries.nl
Text Domain: wp-tour
Domain Path: /languages
*/

class WPTour {
    protected $pluginPath;
    protected $pluginUrl;
    
    /**
     * Construct function for WPTour
     * 
     * $pluginUrl URL to plugin folder
     * $pluginPath Path to current file
     * 
     * Add styles/scripts for plugin and Bootstrap Tour
     * Add and save extra options for Custom Post Type 'tour'
     **/
    public function __construct() {
        // Set Plugin Path
        $this->pluginPath = dirname(__FILE__);
        
        // Set Plugin URL
        $this->pluginUrl = WP_PLUGIN_URL . '/wp-tour';
        
        // Add plugin styles and Bootstrap Tour CDN's for frontend
        if(!is_admin()) {
            add_action('wp_enqueue_scripts', array($this, 'wptourStyles'), 15);
        }
        
        // register 'tour' custom post_type
        add_action( 'init', 'registerTourPosts' );
        
        // add options area for custom post_type 'tour'
        add_action('admin_init', 'admin_init');
        
        // save options for custom post_type 'tour'
        add_action('save_post', 'saveTourOptions');
        
    }
    
    /**
     * Add styles and scripts
     **/
    public function wptourStyles() {
		// set up url to plugin stylesheet
		$url = $this->pluginUrl . '/wptour-styles.css';
		
		// register styles and scripts
		wp_register_style('wptour-style', $this->pluginUrl . '/css/wptour-styles.css');
		wp_register_style('bootstrap-tour-standalone-css', $this->pluginUrl . '/css/bootstrap-tour-standalone.min.css');
		wp_register_script('bootstrap-tour-standalone-js', $this->pluginUrl . '/js/bootstrap-tour-standalone.min.js');
		
		
		
		// enqueue styles and scripts
		wp_enqueue_style('wptour-style');
		wp_enqueue_style('bootstrap-tour-standalone-css');
		wp_enqueue_script('jquery');
		wp_enqueue_script('bootstrap-tour-standalone-js',array( 'jquery' ));
	}
	
	/**
	 * Start the tour on frontend with all the tour posts in wp-admin
	 **/
	public function startTour() {
	    $args = array( 
	        'post_type' => 'tour',
	        'meta_key' => 'order',
	        'order_by' => 'meta_value',
	        'order' => 'ASC'
	    );
	    $tourPosts = query_posts($args);
	    ?>
	    <script>
            // Instance the tour
            var tour = new Tour({
                
                steps: [
                
                <?php 
                foreach ($tourPosts as $tour) {
                ?>
                    {
                        element: '<?php echo $tour->element ?>',
                        placement: '<?php echo $tour->position ?>',
                        title: '<?php echo $tour->post_title ?>',
                        content: '<?php echo $tour->post_content ?>'
                    },
                <?php
                }
                ?>
            ]});
            
            // Initialize the tour
            tour.init();
            
            // Start the tour
            tour.start();
	    </script>
	    <?php
	}
	
}

/**
 * Register 'tour' Custom Post Type
 **/
function registerTourPosts() {
    $labels = array(
        'name' => _x( 'Tours', 'tour' ),
        'singular_name' => _x( 'Tour', 'tour' ),
        'add_new' => _x( 'Nieuwe Tour', 'tour' ),
        'add_new_item' => _x( 'Voeg nieuwe Tour toe', 'tour' ),
        'edit_item' => _x( 'Bewerk Tour', 'tour' ),
        'new_item' => _x( 'Nieuwe Tour', 'tour' ),
        'view_item' => _x( 'Bekijk Tour', 'tour' ),
        'search_items' => _x( 'Zoek Tour', 'tour' ),
        'not_found' => _x( 'Geen Tour gevonden', 'tour' ),
        'not_found_in_trash' => _x( 'Geen Tour gevonden in de prullenbak', 'tour' ),
        'parent_item_colon' => _x( 'Parent Tour:', 'tour' ),
        'menu_name' => _x( 'Tours', 'tour' )
    );

    $args = array(
        'labels' => $labels,
        'public' => true,
        'publicly_queryable' => true,
        'show_ui' => true,
        'show_in_menu' => true,
        'query_var' => true,
        'rewrite' => array('slug' => 'tour'),
        'capability_type' => 'post',
        'has_archive' => true,
        'hierarchical' => false,
        'menu_position' => null,
        'supports' => array( 'title', 'editor', 'author', 'thumbnail', 'excerpt', 'custom-fields')
    );
    register_post_type( 'tour', $args );
}

/**
 * Add options area for Custom Post Type 'tour'
 **/
function admin_init(){
    add_meta_box('tourInfo-meta', 'Tour Options', 'meta_options', 'tour', 'side', 'low');
}

/**
 * Add options in new optionsarea for Custom Post Type 'tour'
 **/
function meta_options(){
    global $post;
    $custom = get_post_custom($post->ID);
    $element = $custom['element'][0];
    $position = $custom['position'][0];
    $order = $custom['order'][0];
    
    echo '<label>Connect to element:</label>';
    echo '<br/><span class="small">(class: .elem, id: #elem)</span>';
    echo '<input name="element" value="' .  $element . '" />';
    echo '<br/><label>Pop up position:</label>';
    echo '<br/><span class="small">(right,left,top,bottom)';
    echo '<input name="position" value="' .  $position . '" />';
    echo '<br/><label>Pop up order:</label>';
    echo '<br/><input name="order" value="' .  $order . '" />';
}

/**
 * Save the options for Custom Post Type 'tour'
 **/
function saveTourOptions(){
    global $post;
    update_post_meta($post->ID, 'element', $_POST['element']);
    update_post_meta($post->ID, 'position', $_POST['position']);
    update_post_meta($post->ID, 'order', $_POST['order']);
}

/**
 * Function to initialize the tour on frontend
 **/
function wpTour() {
    $wpTour = new WPTour();    
    $wpTour->startTour();
}

/**
 * Initialize WPTour
 **/
$wpTour = new WPTour();