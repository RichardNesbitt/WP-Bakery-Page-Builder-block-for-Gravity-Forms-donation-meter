<?php
/*
Plugin Name: WP Bakery Page Builder block for Gravity Forms donation meter
Description: Parent Plugin should be installed and active to use this plugin.
Version: 1.0.0
*/

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

add_action( 'admin_init', 'vc_gf_installed' );

function vc_gf_installed() {
    if ( is_admin() && current_user_can( 'activate_plugins' ) &&  ( !is_plugin_active( 'js_composer/js_composer.php' ) || !is_plugin_active('gravityforms/gravityforms.php')) ) {
        add_action( 'admin_notices', 'need_vc_gf_notice' );

        deactivate_plugins( plugin_basename( __FILE__ ) ); 

        if ( isset( $_GET['activate'] ) ) {
            unset( $_GET['activate'] );
        }
    }
}

function need_vc_gf_notice(){
    ?><div class="error"><p>Sorry, but the donation meter requires WP Bakery Page Builder and Gravity Forms to be installed and active.</p></div><?php
}
 
/*
Element Description: VC Info Box
*/
 
// Element Class 
class vcGFMeter extends WPBakeryShortCode {
     
    // Element Init
    function __construct() {
        add_action( 'init', array( $this, 'vc_gforms_meter_mapping' ) );
        add_shortcode( 'vc_gforms_meter', array( $this, 'vc_gforms_meter_html' ) );
    }
     
    // Element Mapping
    public function vc_gforms_meter_mapping() {

        // get list of gravity forms
        $gravity_forms_array[ __( 'No Gravity forms found.', 'js_composer' ) ] = '';
        if ( class_exists( 'RGFormsModel' ) ) {
            $gravity_forms = RGFormsModel::get_forms( 1, 'title' );
            if ( $gravity_forms ) {
                $gravity_forms_array = array( __( 'Select a form to display.', 'js_composer' ) => '' );
                foreach ( $gravity_forms as $gravity_form ) {
                    $gravity_forms_array[ $gravity_form->title ] = $gravity_form->id;
                }
            }
        }

        vc_map( array(
            'name' => __( 'Gravity Forms Donation Progess Bar', 'js_composer' ),
            'base' => 'vc_gforms_meter',
             'icon' => plugin_dir_url( __FILE__ ) . 'element-icon-donation-meter.svg',
            'category' => __( 'Content', 'js_composer' ),
            'description' => __( 'Animated progress bar', 'js_composer' ),
            'params' => array(
                array(
                    'type' => 'textfield',
                    'heading' => __( 'Widget title', 'js_composer' ),
                    'param_name' => 'title',
                    'description' => __( 'Enter text used as widget title (Note: located above content element).', 'js_composer' ),
                ),
                array(
                    'type' => 'textfield',
                    'heading' => __( 'Label', 'js_composer' ),
                    'param_name' => 'label',
                    'description' => __( 'Enter text used as title of bar.', 'js_composer' ),
                    'admin_label' => true,
                ),
                array(
                    'type' => 'textfield',
                    'heading' => __( 'Goal Amount', 'js_composer' ),
                    'param_name' => 'goalamt',
                    'description' => __( 'Enter the goal amount for your campaign.', 'js_composer' ),
                ),
                array(
                    'type' => 'dropdown',
                    'heading' => __( 'Choose Form', 'js_composer' ),
                    'param_name' => 'gformid',
                    'value' => $gravity_forms_array,
                    'save_always' => true,
                    'description' => __( 'Select a form to track. This form MUST use the Form Total field.', 'js_composer' ),
                    'admin_label' => true,
                ),
                array(
                    'type' => 'dropdown',
                    'heading' => __( 'Color', 'js_composer' ),
                    'param_name' => 'bgcolor',
                    'value' => array(
                            __( 'Classic Grey', 'js_composer' ) => 'bar_grey',
                            __( 'Classic Blue', 'js_composer' ) => 'bar_blue',
                            __( 'Classic Turquoise', 'js_composer' ) => 'bar_turquoise',
                            __( 'Classic Green', 'js_composer' ) => 'bar_green',
                            __( 'Classic Orange', 'js_composer' ) => 'bar_orange',
                            __( 'Classic Red', 'js_composer' ) => 'bar_red',
                            __( 'Classic Black', 'js_composer' ) => 'bar_black',
                        ) + getVcShared( 'colors-dashed' ) + array(
                            __( 'Custom Color', 'js_composer' ) => 'custom',
                        ),
                    'description' => __( 'Select bar background color.', 'js_composer' ),
                    'admin_label' => true,
                    'param_holder_class' => 'vc_colored-dropdown',
                ),
                array(
                    'type' => 'colorpicker',
                    'heading' => __( 'Bar custom background color', 'js_composer' ),
                    'param_name' => 'custombgcolor',
                    'description' => __( 'Select custom background color for bars.', 'js_composer' ),
                    'dependency' => array(
                        'element' => 'bgcolor',
                        'value' => array( 'custom' ),
                    ),
                ),
                array(
                    'type' => 'checkbox',
                    'heading' => __( 'Options', 'js_composer' ),
                    'param_name' => 'options',
                    'value' => array(
                        __( 'Add stripes', 'js_composer' ) => 'striped',
                        __( 'Add animation (Note: visible only with striped bar).', 'js_composer' ) => 'animated',
                    ),
                ),
                vc_map_add_css_animation(),
                array(
                    'type' => 'el_id',
                    'heading' => __( 'Element ID', 'js_composer' ),
                    'param_name' => 'el_id',
                    'description' => sprintf( __( 'Enter element ID (Note: make sure it is unique and valid according to <a href="%s" target="_blank">w3c specification</a>).', 'js_composer' ), 'http://www.w3schools.com/tags/att_global_id.asp' ),
                ),
                array(
                    'type' => 'textfield',
                    'heading' => __( 'Extra class name', 'js_composer' ),
                    'param_name' => 'el_class',
                    'description' => __( 'Style particular content element differently - add a class name and refer to it in custom CSS.', 'js_composer' ),
                ),
                array(
                    'type' => 'css_editor',
                    'heading' => __( 'CSS box', 'js_composer' ),
                    'param_name' => 'css',
                    'group' => __( 'Design Options', 'js_composer' ),
                ),
            ),
        ));                          

    } 
     
    // Element HTML
    public function vc_gforms_meter_html( $atts ) {
         
        $title = $values = $units = $bgcolor = $css = $custombgcolor = $customtxtcolor = $options = $el_class = $el_id = $css_animation = $output = $wrapper_attributes = '';
        
        extract( $atts );
        $options = explode( ',', $options);
        $el_class = $this->getExtraClass( $el_class ) . $this->getCSSAnimation( $css_animation );
        
        //wp_enqueue_script( 'waypoints' );
        wp_register_style( 'donation_bar', plugins_url( 'style.css', __FILE__ ) );
        wp_enqueue_style( 'donation_bar' );
        
        print plugin_dir_path( 'style.css' ) . 'style.css' ;


        if ( $bgcolor === 'custom' && $custombgcolor !== '' ) {
            $custombgcolor = ' style="' . vc_get_css_color( 'background-color', $custombgcolor ) . '"';
            if ( $customtxtcolor !== '') {
                $customtxtcolor = ' style="' . vc_get_css_color( 'color', $customtxtcolor ) . '"';
            }
            $bgcolor = '';
        } else {
            $bgcolor = 'vc_progress-bar-color-' . esc_attr( $bgcolor );
            $custombgcolor = '';
            $customtxtcolor = '';
            $el_class .= ' ' . $bgcolor;
        }

        $class_to_filter = 'vc_progress_bar wpb_content_element';
        $class_to_filter .= vc_shortcode_custom_css_class( $css, ' ' ) . $this->getExtraClass( $el_class );
        $css_class = apply_filters( VC_SHORTCODE_CUSTOM_CSS_FILTER_TAG, $class_to_filter, $this->settings['base'], $atts );
        
        if ( ! empty( $el_id ) ) {
            $wrapper_attributes = 'id="' . esc_attr( $el_id ) . '"';
        }
        
        //find total field
        $form = GFAPI::get_form( $gformid );
        $fields = $form['fields'];
        $field_id = '';
        foreach( $fields as $field ):
            if ( $field['type'] == 'product' ): 
                $field_id = $field['id'];
            endif;
        endforeach;
        
        //add up totals from all form submissions
        $entries = GFAPI::get_entries( $gformid, $total_count = 0 );
        $total_donations = 0.00;
        foreach( $entries as $entry ){
            $total_donations += floatval(str_replace('$','',$entry[$field_id]));
        }
        
        //set bar percentage
        $percentage_value = ( ($total_donations / $goalamt * 100) > 100 ? 100 : $total_donations / $goalamt * 100 );
        
        $line = array();
        $line['label'] = isset( $label ) ? $label : '';
        $line['bgcolor'] = isset( $color ) && $color !== 'custom' ? '' : $custombgcolor;
        $line['txtcolor'] = isset( $color ) && $color !== 'custom' ? '' : $customtxtcolor;
        
        if ( isset( $customcolor ) && ( ! isset( $color ) || 'custom' === $color ) ) {
            $line['bgcolor'] = ' style="background-color: ' . esc_attr( $customcolor ) . ';"';
        }
        if ( isset( $customtxtcolor ) && ( ! isset( $color ) || 'custom' === $color ) ) {
            $line['txtcolor'] = ' style="color: ' . esc_attr( $customtxtcolor ) . ';"';
        }
        
        
        $output = '<div class="' . esc_attr( $css_class ) . '" ' . $wrapper_attributes . '>';

            $output .= wpb_widget_title( array( 'title' => $title, 'extraclass' => 'wpb_progress_bar_heading' ) );

                $output .= '<div class="vc_general vc_single_bar vc_donation_bar' . ( ( isset( $line['color'] ) && 'custom' !== $line['color'] ) ? ' vc_progress-bar-color-' . $line['color'] : '' )
            . '">';       

                $output .= '<span class="vc_bar ' . esc_attr( implode( ' ', $options ) ) . '" data-percentage-value="' . esc_attr( $percentage_value ) . '" data-value="' . esc_attr( $line['value'] ) . '"' . $line['bgcolor'] . '></span>';
            $output .= '</div>';
        $output .= '<small class="vc_label"' . $line['txtcolor'] . '>$' . $total_donations . ' / $' . $goalamt . '</small>';


        $output .= '</div>';

        return $output;
         
    } 
     
} // End Element Class
 
// Element Class Init
new vcGFMeter();  
