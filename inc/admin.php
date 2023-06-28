<?php

/**
 * Add styles and scripts in backend.
 *
 * @return void
 */
function rh_categories_admin_enqueue_scripts(): void {
    // admin-specific styles.
    wp_enqueue_style('rh-categories',
        plugin_dir_url(RH_CATEGORIES) . '/admin/style.css',
        array(),
        filemtime(plugin_dir_path(RH_CATEGORIES) . '/admin/style.css'),
    );

    // add extended color-picker.
    wp_enqueue_style( 'wp-color-picker' );
    wp_register_script(
        'wp-color-picker-alpha',
        plugins_url( '/lib/wp-color-picker-alpha.min.js' , RH_CATEGORIES ),
        array( 'wp-color-picker' ),
        filemtime(plugin_dir_path(RH_CATEGORIES) . '/lib/wp-color-picker-alpha.min.js')
    );
    wp_enqueue_script( 'wp-color-picker-alpha' );

    // add JS.
    wp_enqueue_script( 'rh-categories',
        plugins_url( '/admin/js.js' , RH_CATEGORIES ),
        array( 'jquery' ),
        filemtime(plugin_dir_path(RH_CATEGORIES) . '/admin/js.js'),
    );
}
add_action( 'admin_enqueue_scripts', 'rh_categories_admin_enqueue_scripts', 10, 1 );

/**
 * Add columns for category-table.
 */
function rh_categories_add_category_columns( $columns ): array {
    $new_columns = array();

    // move checkbox-field in first row
    if (isset($columns['cb'])) {
        $new_columns['cb'] = $columns['cb'];
        unset($columns['cb']);
    }

    // add column with empty title for our color-column.
    $new_columns['rh-categories-color'] = __('Color', 'rh-categories');

    // return resulting column-list.
    return array_merge( $new_columns, $columns);
}
add_filter( 'manage_edit-category_columns', 'rh_categories_add_category_columns', 10, 1);

/**
 * Add content for our custom column.
 *
 * @param $columns
 * @param $column
 * @param $term_id
 * @return void
 * @noinspection PhpUnusedParameterInspection
 */
function rh_categories_add_category_column( $columns, $column, $term_id ): void {
    if( 'rh-categories-color' === $column ) {
        $value = get_term_meta( $term_id, 'rh-cat-color', true );
        ?><div class="rh-cat-color" style="background-color: <?php echo $value; ?>">&nbsp;</div><?php
    }
}
add_filter( 'manage_category_custom_column', 'rh_categories_add_category_column', 10, 3);

/**
 * Extend form if new category term is added.
 *
 * @return void
 */
function rh_categories_add_category(): void {
    rh_categories_edit_category_fields();
}
add_action( 'category_add_form_fields', 'rh_categories_add_category' );

/**
 * Extend form if category is edited.
 *
 * @param $term
 * @return void
 */
function rh_categories_edit_category( $term ): void {
    rh_categories_edit_category_fields( $term );
}
add_action( 'category_edit_form_fields', 'rh_categories_edit_category' );

/**
 * Output fields for edit-form of categories.
 *
 * @param WP_Term|false $term
 * @return void
 */
function rh_categories_edit_category_fields( WP_Term|false $term = false ): void {
    if( $term ) {
        // get the color.
        $color = get_term_meta( $term->term_id, 'rh-cat-color', true );

        // get the image.
        $image = absint(get_term_meta( $term->term_id, 'rh-cat-image', true ));

        // output.
        ?>
        <tr class="form-field rh-category-color">
            <th scope="row"><label
                    for="rh-category-color"><?php echo esc_html__( 'Set color', 'rh-categories' ); ?></label></th>
            <td>
                <input name="rh-category-color" id="rh-category-color" type="text"
                       class="rh-category-color-picker" value="<?php echo esc_attr($color); ?>"
                       data-default-color="" data-alpha-enabled="true" data-alpha-color-type="hex"
                       size=""  placeholder="">
            </td>
        </tr>
        <tr class="form-field rh-category-image">
            <th scope="row"><label
                    for="rh-category-image"><?php echo esc_html__( 'Choose Image', 'rh-categories' ); ?></label></th>
            <td<?php if( 0 === $image ) { ?> class="no-image"<?php } ?>>
                <?php
                    if( $image > 0 ) {
                        echo wp_get_attachment_image( $image );
                    }
                ?>
                <a href="#" class="rh-category-img-upl"><?php echo __('Upload image', 'rh-categories'); ?></a>
                <a href="#" class="rh-category-img-remove"><?php echo __('Remove image', 'rh-categories'); ?></a>
                <input type="hidden" name="rh-category-image" value="<?php echo $image; ?>">
            </td>
        </tr>
        <?php
    }
    else {
        ?>
        <div class="form-field term-rh-category-color-wrap">
            <label for="rh-category-color"><?php echo esc_html__( 'Color', 'rh-categories' ); ?></label>
            <input name="rh-category-color" id="rh-category-color" type="text"
                   class="rh-category-color-picker" value=""
                   data-default-color="" data-alpha-enabled="true" data-alpha-color-type="hex"
                   size=""  placeholder="">
        </div>
        <?php
    }
}

/**
 * Save color setting on category.
 *
 * @param $term_id
 * @param $tt_id
 * @param $taxonomy
 * @return void
 * @noinspection PhpMissingParamTypeInspection
 * @noinspection PhpUnusedParameterInspection
 */
function rh_categories_save_category( $term_id, $tt_id = '', $taxonomy = '' ): void {
    if( 'category' === $taxonomy ) {
        update_term_meta( $term_id, 'rh-cat-color', sanitize_hex_color($_POST['rh-category-color']) );
        update_term_meta( $term_id, 'rh-cat-image', absint($_POST['rh-category-image']) );
    }
}
add_action( 'created_term', 'rh_categories_save_category', 10, 3);
add_action( 'edit_term', 'rh_categories_save_category', 10, 3);
