<?php
/**
 * File to add custom dynamic tag on elementor.
 */

namespace Rheinhessen\Elementor;

use Elementor\Core\DynamicTags\Tag;
use Elementor\Modules\DynamicTags\Module;

/**
 * Define the custom dynamic tag.
 */
Class Main_Category_on_Post extends Tag {

    /**
     * Get dynamic tag name.
     *
     * Retrieve the name of the random number tag.
     *
     * @since 1.0.0
     * @access public
     * @return string Dynamic tag name.
     */
    public function get_name(): string {
        return 'rh-main-category-color';
    }

    /**
     * Get dynamic tag title.
     *
     * Returns the title of the random number tag.
     *
     * @since 1.0.0
     * @access public
     * @return string Dynamic tag title.
     */
    public function get_title(): string {
        return esc_html__( 'Main category color', 'rh-categories' );
    }

    /**
     * Get dynamic tag groups.
     *
     * Retrieve the list of groups the random number tag belongs to.
     *
     * @since 1.0.0
     * @access public
     * @return array Dynamic tag groups.
     */
    public function get_group(): array {
        return array( 'post' );
    }

    /**
     * Get dynamic tag categories.
     *
     * Retrieve the list of categories the random number tag belongs to.
     *
     * @since 1.0.0
     * @access public
     * @return array Dynamic tag categories.
     */
    public function get_categories(): array {
        return array( Module::COLOR_CATEGORY );
    }

    /**
     * Render tag output on the frontend.
     *
     * Written in PHP and used to generate the final HTML.
     *
     * @since 1.0.0
     * @access public
     * @return void
     */
    public function render(): void {
        // only return value if it is used in a post-object.
        if( 'post' === get_post_type( get_the_ID() ) ) {
            // get primary color defined via yoast-settings.
            $primary_term_id = absint(get_post_meta( get_the_ID(), '_yoast_wpseo_primary_category', true ));
            if( $primary_term_id > 0 ) {
                // get the color set on this category.
                echo get_term_meta( $primary_term_id, 'rh-cat-color', true );
            }
            else {
                // get first assigned category for this post.
                $terms = wp_get_post_terms( get_the_ID(), 'category' );
                if( !empty($terms) ) {
                    // get their color.
                    echo get_term_meta( $terms[0]->term_id, 'rh-cat-color', true );
                }
            }
        }
    }
}
