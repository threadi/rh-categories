<?php

namespace Rheinhessen\Elementor;

use Elementor\Controls_Manager;
use Elementor\Widget_Base;
use Exception;
use WP_Term_Query;

/**
 * Object to handle the filter-widget for position list-view.
 *
 * @noinspection PhpUnused
 */
class Post_Filter extends Widget_Base {
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
        return 'rh-categories-post-filter';
    }

    /**
     * Retrieve the widget title.
     *
     * @return string Widget title.
     */
    public function get_title(): string
    {
        return __( 'Post Filter', 'rh-categories' );
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
        $html = '';
        // show both on frontpage.
        if( is_front_page() ) {
            $html .= $this->get_category_filter();
            $html .= $this->get_tag_filter();
        }
        // show tag filter on category-page.
        elseif( is_category() ) {
            $html .= $this->get_tag_filter();
        }
        // show category filter on tag-page.
        elseif( is_tag() ) {
            $html .= $this->get_category_filter();
        }
        if( !empty($html) ) {
            // output filter-form.
            ?>
            <form action="" method="get" class="rh-categories-filter" id="rh-cat-filter">
                <a class="mobile-button-open" href="#rh-cat-filter"><?php echo __( 'Filter', 'rh-categories' ); ?></a>
                <a class="mobile-button-close" href="#">&nbsp;</a>
                <div>
                    <?php
                        echo $html;
                    ?>
                    <button type="submit"><?php echo __( 'Filter', 'rh-categories' ); ?></button>
                </div>
            </form>
            <?php
        }
    }

    /**
     * Get category-filter.
     *
     * @return string
     */
    private function get_category_filter(): string {
        // get all categories with images and colors.
        $query = array(
            'taxonomy' => 'category',
            'orderby' => 'name',
            'order' => 'ASC',
            'meta_query' => array(
                'relation' => 'AND',
                array(
                    'key' => 'rh-cat-color',
                    'value' => '',
                    'compare' => 'NOT IN'
                ),
                array(
                    'key' => 'rh-cat-image',
                    'value' => '',
                    'compare' => 'NOT IN'
                )
            )
        );
        $results = new WP_Term_Query( $query );

        // get results.
        $terms = $results->terms;
        if( !empty($terms) ) {
            ob_start();
            ?>
            <h2><?php echo __( 'Categories', 'rh-categories' ); ?></h2>
            <ul class="rh-categories">
                <?php
                // loop through the terms.
                foreach( $terms as $term ) {
                    // get image for this category.
                    $attachment_id = absint(get_term_meta($term->term_id, 'rh-cat-image', true ));
                    if( $attachment_id > 0 ) {
                        // set marker if this filter is set.
                        $checked = !empty($_GET['categories'][$term->term_id]) ? ' checked="checked"' : '';

                        // get image.
                        $image = wp_get_attachment_image_url( $attachment_id );

                        // output item.
                        ?><li><label for="rh-cat-<?php echo $term->term_id; ?>"><input type="checkbox"<?php echo $checked; ?> id="rh-cat-<?php echo $term->term_id; ?>" name="categories[<?php echo $term->term_id; ?>]" value="<?php echo $term->term_id; ?>"><img src="<?php echo esc_url($image); ?>" alt="<?php echo esc_attr($term->name); ?>"><span><?php echo esc_attr($term->name); ?></span></label></li><?php
                    }
                }
                ?>
            </ul>
            <?php
            $content = ob_get_contents();
            ob_end_clean();
            return $content;
        }

        // return nothing of no category was found.
        return '';
    }

    /**
     * Get tag filter.
     *
     * @return string
     */
    private function get_tag_filter(): string {
        // get all categories with images and colors.
        $query = array(
            'taxonomy' => 'post_tag',
            'orderby'    => 'count',
            'order'      => 'DESC',
            'hide_empty' => true,
            'number' => 8
        );
        $results = new WP_Term_Query( $query );

        // get results.
        $terms = $results->terms;
        if( !empty($terms) ) {
            $ordered_terms = array();
            foreach( $terms as $term ) {
                $ordered_terms[$term->name] = $term;
            }
            ksort($ordered_terms);

            // create list.
            ob_start();
            ?>
            <h2><?php echo __( 'Tags', 'rh-categories' ); ?></h2>
            <ul class="rh-tags"><?php

                // loop through the terms.
                foreach( $ordered_terms as $term ) {
                    // set marker if this filter is set.
                    $checked = !empty($_GET['tags'][$term->term_id]) ? ' checked="checked"' : '';

                    // output item.
                    ?><li><label for="rh-tag-<?php echo $term->term_id; ?>"><input type="checkbox"<?php echo $checked; ?> id="rh-tag-<?php echo $term->term_id; ?>" name="tags[<?php echo $term->term_id; ?>]" value="<?php echo $term->term_id; ?>"><?php echo esc_attr($term->name); ?></label></li><?php
                }

            // end list.
            ?></ul><?php
            $content = ob_get_contents();
            ob_end_clean();
            return $content;
        }

        // return nothing if no tags were found.
        return '';
    }
}