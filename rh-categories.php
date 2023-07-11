<?php
/**
 * Plugin Name:       Rheinhessen Categories
 * Description:       Erweitert die Ausgabe von Kategorien um Farben und Bilder. Ergänzt einen Filter hierfür.
 * Requires at least: 6.2.2
 * Requires PHP:      8.0
 * Version:           1.0.0
 * Author:            laOlaWeb
 * Author URI:		  https://laolaweb.com
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       rh-categories
 */

use Elementor\Plugin;

const RH_CATEGORIES = __FILE__;

// embed necessary files.
require_once 'inc/autoload.php';

// include admin-only files.
if( is_admin() ) {
    require_once 'inc/admin.php';
}

/**
 * General initialization.
 *
 * @return void
 * @noinspection PhpUnused
 */
function rh_categories_init(): void {
    // translations
    load_plugin_textdomain( 'rh-categories', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
}
add_action( 'init', 'rh_categories_init', -1 );

/**
 * Load Elementor-support.
 */
if ( did_action( 'elementor/loaded' ) ) {
    add_action( 'init', 'rh_categories_add_elementor_widgets', 10);
    add_action( 'init', 'rh_categories_ajax' );
}

/**
 * Add Elementor-specific hooks.
 *
 * @return void
 */
function rh_categories_add_elementor_widgets(): void {
    add_action('elementor/widgets/register', 'rh_categories_register_elementor_widgets');
}

function rh_categories_ajax(): void {
    add_action('wp_ajax_nopriv_rh_categories_filter', 'rh_categories_filter');
    add_action('wp_ajax_rh_categories_filter', 'rh_categories_filter');
}

/**
 * Load our custom widget.
 *
 * @return void
 */
function rh_categories_register_elementor_widgets(): void {
    Plugin::instance()->widgets_manager->register(new Rheinhessen\Elementor\Post_Active_Filter());
    Plugin::instance()->widgets_manager->register(new Rheinhessen\Elementor\Post_Filter());
}

/**
 * Frontend-Styles.
 *
 * @return void
 */
function rh_categories_enqueue_scripts(): void {
    // embed style.
    wp_enqueue_style('rh-categories',
        plugin_dir_url(RH_CATEGORIES) . '/css/filter.css',
        array(),
        filemtime(plugin_dir_path(RH_CATEGORIES) . '/css/filter.css'),
    );

    // embed js.
    wp_enqueue_script('rh-categories',
        plugin_dir_url(RH_CATEGORIES) . '/js/filter.js',
        array( 'jquery' ),
        filemtime(plugin_dir_path(RH_CATEGORIES) . '/js/filter.js'),
    );

    // add php-vars to our js-script
    wp_localize_script( 'rh-categories', 'rh_categories_js', array(
        'ajax_url' => admin_url( 'admin-ajax.php' ),
        'filter_nonce' => wp_create_nonce( 'rh-categories-filter' ),
    ));
}
add_action( 'wp_enqueue_scripts', 'rh_categories_enqueue_scripts', 10, 1 );

/**
 * Add our custom tag to get the color of the main category on a single post.
 */
add_action( 'elementor/dynamic_tags/register_tags', function( $dynamic_tags ) {
    $dynamic_tags->register( new Rheinhessen\Elementor\Main_Category_on_Post );
} );

/**
 * Set filter in main query.
 *
 * @param $query
 * @return void
 */
function rh_categories_set_filter( $query ): void {
    if( !empty($query->query['post_type']) && 'post' === $query->query['post_type'] ) {
        if (!empty($_GET['categories']) && is_array($_GET['categories'])) {
            $query->set('category__and', array_map('absint', $_GET['categories']));
        }
        if (!empty($_GET['tags']) && is_array($_GET['tags'])) {
            $query->set('tag__and', array_map('absint', $_GET['tags']));
        }
    }
    if( defined('DOING_AJAX') ) {
        if( !empty($_POST['categories']) && is_array($_POST['categories']) ) {
            $query->set('category__and', array_map( 'absint', $_POST['categories'] ));
        }
        if( !empty($_POST['tags']) && is_array($_POST['tags']) ) {
            $query->set('tag__and', array_map( 'absint', $_POST['tags'] ));
        }
    }
}
add_action( 'pre_get_posts', 'rh_categories_set_filter' );

/**
 * Filter for given categories and tags and return resulting html-code.
 *
 * @return void
 * @noinspection PhpNoReturnAttributeCanBeAddedInspection
 */
function rh_categories_filter(): void {
    // check nonce
    check_ajax_referer('rh-categories-filter', 'nonce');

    // run elementor template to show this single post.
    echo do_shortcode('[elementor-template id="10741"]'); // TODO

    // return nothing more.
    exit;
}

/**
 * Show list of categories as images.
 *
 * @return string
 */
function rh_post_categories( $attributes = array() ): string {
    // get all categories with images on this post.
    $terms = wp_get_object_terms( get_the_ID(), 'category' );

    ob_start();
    ?><ul class="rh-categories"><?php
    foreach( $terms as $term ) {
        // get image for this category.
        $attachment_id = absint(get_term_meta($term->term_id, 'rh-cat-image', true ));
        if( $attachment_id > 0 ) {
            // get image.
            $image = wp_get_attachment_image_url( $attachment_id );

            // add title if set on shortcode.
            $title = '';
            if( is_array($attributes) && !empty($attributes['title']) ) {
                $title = '<span>'.esc_html($term->name).'</span>';
            }

            // output item.
            ?><li><a href="<?php echo esc_url(get_term_link($term->term_id)); ?>"><img src="<?php echo esc_url($image); ?>" alt="<?php echo esc_attr($term->name); ?>"><?php echo $title; ?></a></li><?php
        }
    }
    ?>
    </ul>
    <?php
    $content = ob_get_contents();
    ob_end_clean();
    return $content;
}
add_shortcode( 'rh_post_cats', 'rh_post_categories' );