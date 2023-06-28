<?php

namespace Rheinhessen\Elementor;

use Elementor\Widget_Base;
use Exception;
use WP_Term;

/**
 * Object to handle the filter-widget for position list-view.
 *
 * @noinspection PhpUnused
 */
class Post_Active_Filter extends Widget_Base {
    /**
     * Class constructor.
     *
     * @param array $data Widget data.
     * @param array $args Widget arguments.
     * @throws Exception
     */
    public function __construct( $data = array(), $args = null ) {
        parent::__construct( $data, $args );
    }

    /**
     * Retrieve the widget name.
     *
     * @return string Widget name.
     */
    public function get_name(): string
    {
        return 'rh-categories-post-active-filter';
    }

    /**
     * Retrieve the widget title.
     *
     * @return string Widget title.
     */
    public function get_title(): string
    {
        return __( 'Post Active Filter', 'rh-categories' );
    }

    /**
     * Set keywords for elementor-internal search for widgets.
     *
     * @return string[]
     */
    public function get_keywords(): array
    {
        return array('Rheinhessen', 'Filter', 'Post');
    }

    /**
     * Register the widget controls.
     *
     * @access protected
     */
    protected function register_controls() {}

    /**
     * Render the widget output in Elementor and frontend.
     */
    protected function render(): void {
        if( !empty($_GET['categories']) ) {
            // get query, if set.
            $category_filter = (array)$_GET['categories'];

            if( !empty($category_filter) ) {
                // create list.
                ?><ul class="rh-categories-active-filter"><?php

                // show one entry per filter.
                foreach( $category_filter as $key => $term_id ) {
                    // get the term.
                    $term = get_term( $term_id,'category' );
                    if( $term instanceof WP_Term ) {
                        // get its color.
                        $color = get_term_meta( $term->term_id, 'rh-cat-color', true );

                        // define link to remove this category from filter.
                        $new_params = $category_filter;
                        unset($new_params[$key]);
                        $link = add_query_arg( 'categories', $new_params );

                        // output.
                        ?><li style="background-color: <?php echo $color; ?>"><?php echo esc_html($term->name); ?><a href="<?php echo esc_url($link); ?>">X</a></li><?php
                    }
                }
                ?></ul><?php
            }
        }
    }
}
