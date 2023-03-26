<?php
/*
Plugin Name: WooCommerce Ask for Product
Description: A plugin that changes add to cart button to ask for product button
Author: Patryk Grzesik
*/
if (!defined('ABSPATH')) {
    exit;
}
require_once 'wafp-menu.php';

function wafp_ask_activate() {
    add_action( 'admin_menu', 'wafp_menu' );
}

register_activation_hook( __FILE__, 'wafp_ask_activate' );

function wafp_ask_deactivate() {
    remove_menu_page( 'email_settings' );
}

register_deactivation_hook( __FILE__, 'wafp_ask_deactivate' );

add_action('woocommerce_after_shop_loop_item', 'wafp_price_and_stock', 10);
add_action('woocommerce_single_product_summary', 'wafp_price_and_stock', 30);

function wafp_price_and_stock() {
    global $product;
    
    $price = $product->get_price();
    $stock_quantity = $product->get_stock_quantity();
    $brands = wp_get_post_terms( get_the_ID(),'pa_brand');
    $categories = wp_get_post_terms( get_the_ID(),'product_cat');
    $brands_id = [];
    $categories_id = [];
    $i=0;
    foreach($brands as $brand){
        $brands_id[$i] = $brand->term_id;  
        $i++;
    }
    $i=0;
    foreach($categories as $categorie){
        $categories_id[$i] = $categorie->term_id;  
        $i++;
    }
    if ( $price == '' || $stock_quantity == 0 ) {
        remove_action('woocommerce_after_shop_loop_item', 'woocommerce_template_loop_add_to_cart', 10);
        add_action('woocommerce_after_shop_loop_item', 'wafp_ask_for_product_button', 30);
        remove_action('woocommerce_single_product_summary', 'woocommerce_template_single_add_to_cart', 10);
        add_action('woocommerce_single_product_summary', 'wafp_ask_for_product_button', 30);
        
        add_action('wafp_modal', 'wafp_form_modal',10,2);
        do_action('wafp_modal',$brands_id,$categories_id);
    }
}

function wafp_ask_for_product_button() {
    echo '<button type="button" class="button" id="ask_for_product_button">ZAPYTAJ O PRODUKT</button>';
}

add_action('wp_head', 'wafp_form_styles');
function wafp_form_styles() {
    ?>
    <style>
        #ask-question-form {
            text-align:center;
        }
        #ask-question-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 9999;
        }
        #ask-question-modal {
            display: none;
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background-color: #fff;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.5);
            z-index: 10000;
        }
        #ask-question-close {
            position: absolute;
            top: 10px;
            right: 10px;
            font-size: 20px;
            cursor: pointer;
        }
        .ask-question {
            cursor: pointer;
        }
    </style>
    <?php
}

function wafp_form_modal($brands_id,$categories_id) {
    ?>
    <div id="ask-question-overlay">
        <div id="ask-question-modal">
            <div id="ask-question-close">&times;</div>
            <h2>Zadaj pytanie o produkt</h2>
            <form id="ask-question-form" method="post">
                <label for="ask_question_name">Imię i nazwisko:</label><br>
                <input type="text" name="ask_question_name" id="ask_question_name" required><br>

                <label for="ask_question_phone">Telefon kontaktowy:</label><br>
                <input type="text" name="ask_question_phone" id="ask_question_phone" required><br>

                <label for="ask_question_subject">Temat:</label><br>
                <input type="text" name="ask_question_subject" id="ask_question_subject" readonly required><br>

                <label for="ask_question_message">Treść pytania:</label><br>
                <textarea name="ask_question_message" id="ask_question_message" required></textarea><br><br>

                <input type="submit" name="submit" value="Wyślij">
            </form>
        </div>
    </div>
    <?php
    if(isset($_POST['submit'])){
        do_action('wp_ajax_ask_question',$categories_id,$brands_id);
        do_action('wp_ajax_nopriv_ask_question',$categories_id,$brands_id);
    }
}

add_action('wp_footer', 'wafp_form_scripts');
function wafp_form_scripts() {
    ?>
    <script>
        jQuery( document ).ready( function( $ ) {
            var $product_name = $('.woocommerce-loop-product__title').text();
            var $product_url = $('.woocommerce-loop-product__link').attr('href');

            $('#ask_question_subject').val('Pytanie o produkt: ' + $product_name  + ' Link do produktu: ' + $product_url);

            $('#ask_for_product_button').click( function() {
                $('#ask-question-overlay').fadeIn();
                $('#ask-question-modal').fadeIn();
            });

            $( '#ask-question-close' ).click( function() {
                $('#ask-question-overlay').fadeOut();
                $('#ask-question-modal').fadeOut();
            });

            $( window ).click( function( event ) {
                if (event.target == document.getElementById('ask-question-overlay') ) {
                    $('#ask-question-overlay').fadeOut();
                    $('#ask-question-modal').fadeOut();
                }
            });
        });
    </script>
    <?php
}

add_action('wp_ajax_ask_question', 'wafp_form_send_email',10,2);
add_action('wp_ajax_nopriv_ask_question', 'wafp_form_send_email',10,2);
function wafp_form_send_email($categories_id,$brands_id) {

    if (trim($_POST['ask_question_name']) == '' || trim($_POST['ask_question_phone']) == '' || trim($_POST['ask_question_subject']) == '' || trim($_POST['ask_question_message']) == '') {
        wp_send_json_error('Wszystkie pola są wymagane.');
    }

    $name = sanitize_text_field( $_POST['ask_question_name'] );
    $phone = sanitize_text_field( $_POST['ask_question_phone'] );
    $subject = sanitize_text_field( $_POST['ask_question_subject'] );
    $message = wp_kses_post( $_POST['ask_question_message'] );

    $body = "Imię i nazwisko: $name\nTelefon: $phone\nTemat: $subject\nWiadomość: $message\n\n";
    $headers = array( 'Content-Type: text/html; charset=UTF-8' );

    foreach ($categories_id as $categories_id) {
        wp_mail(get_option('category_email_settings')[intval($categories_id)], $subject, $body, $headers);
    }
    foreach ($brands_id as $brands_id) {
        wp_mail(get_option('brand_email_settings' )[intval($brands_id)], $subject, $body, $headers);
    }
    header("Refresh:0");
    unset($_POST['submit']);
    wp_die();
}