<?php
add_action( 'admin_menu', 'wafp_menu' );

function wafp_menu() {
    add_menu_page( 
        __( 'Maile do kategorii/marki', 'textdomain' ),
        'Maile do kategorii/marki',
        'manage_options',
        'email_settings',
        'wafp_create_menu',
        'dashicons-email-alt',
        100
    );
}

function wafp_create_menu() {
    ?>
    <div class="wrap">
        <h2><?php _e( 'Maile do kategorii', 'textdomain' ); ?></h2>
        <form method="post" action="options.php">
            <?php settings_fields( 'category_email_settings_group' ); ?>
            <?php do_settings_sections( 'category_email_settings_page' ); ?>
            <?php submit_button(); ?>
        </form>
        <h2><?php _e( 'Maile do marki', 'textdomain' ); ?></h2>
        <form method="post" action="options.php">
            <?php settings_fields( 'brand_email_settings_group' ); ?>
            <?php do_settings_sections( 'brand_email_settings_page' ); ?>
            <?php submit_button(); ?>
        </form>
    </div>
    <?php
}

add_action( 'admin_init', 'category_register_my_settings' );

function category_register_my_settings() {
    register_setting( 'category_email_settings_group', 'category_email_settings' );
    add_settings_section( 'category_email_section', __( 'Przypisz maile do kategorii', 'textdomain' ), 'category_email_section_callback', 'category_email_settings_page' );
    $categories = get_terms( 'product_cat');
    foreach ( $categories as $category ) {
        add_settings_field(
            'category_email_' . $category->term_id,
            $category->name,
            'category_email_callback',
            'category_email_settings_page',
            'category_email_section',
            array(
                'id' => 'category_email_' . $category->term_id,
                'name' => 'category_email_settings[' . $category->term_id . ']',
                'value' => get_option( 'category_email_settings' )[$category->term_id]
            )
        );
    }
}

function category_email_section_callback() {
    echo '<p>' . __( 'Przypisz maile:', 'textdomain' ) . '</p>';
}

function category_email_callback( $args ) {
    $id = $args['id'];
    $name = $args['name'];
    $value = $args['value'];
    echo '<input type="email" id="' . $id . '" name="' . $name . '" value="' . $value . '" />';
}

add_action( 'admin_init', 'brand_register_my_settings' );



function brand_register_my_settings() {
    register_setting( 'brand_email_settings_group', 'brand_email_settings' );
    add_settings_section( 'brand_email_section', __( 'Przypisz maile do kategorii', 'textdomain' ), 'brand_email_section_callback', 'brand_email_settings_page' );
    $brands = get_terms('pa_brand');
    foreach ($brands  as $brand) {
        add_settings_field(
            'brand_email_' . $brand->term_id,
            $brand->name,
            'brand_email_callback',
            'brand_email_settings_page',
            'brand_email_section',
            array(
                'id' => 'brand_email_' . $brand->term_id,
                'name' => 'brand_email_settings[' . $brand->term_id . ']',
                'value' => get_option( 'brand_email_settings' )[$brand->term_id]
            )
        );
    }
}

function brand_email_section_callback() {
    echo '<p>' . __( 'Przypisz maile:', 'textdomain' ) . '</p>';
}

function brand_email_callback( $args ) {
    $id = $args['id'];
    $name = $args['name'];
    $value = $args['value'];
    echo '<input type="email" id="' . $id . '" name="' . $name . '" value="' . $value . '" />';
}