<?php
/**
 * Plugin Name: MPH Comparison Slider
 * Plugin URI:  https://github.com/mike-asuncion/mph-comparison-slider
 * Description: Adds a draggable design-vs-HTML comparison slider to portfolio projects. Upload a design screenshot (Figma, Sketch, XD, etc.) and a build (HTML) screenshot, then drag to reveal one over the other. Includes browser-chrome and design-tool toolbar decorations, side label badges, and vanilla-JS pointer/touch/keyboard interaction.
 * Version:     1.0.2
 * Author:      Mike Asuncion
 * Author URI:  https://mikeasuncion.ph
 * License:     GPL-2.0-or-later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: mph-cs
 *
 * Co-developed with Claude Code (Anthropic Claude Opus 4.7).
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

define( 'MPH_CS_VERSION', '1.0.4' );
define( 'MPH_CS_PATH', plugin_dir_path( __FILE__ ) );
define( 'MPH_CS_URL', plugin_dir_url( __FILE__ ) );
define( 'MPH_CS_POST_TYPE', 'portfolio_project' );

require_once MPH_CS_PATH . 'includes/class-meta-box.php';
require_once MPH_CS_PATH . 'includes/class-renderer.php';

add_action( 'plugins_loaded', function () {
    new MPH_CS_Meta_Box();
    new MPH_CS_Renderer();
} );

/**
 * Public API: does this post have both screenshots?
 */
function mph_has_comparison_screenshots( $post_id ) {
    $html   = get_post_meta( $post_id, '_mph_html_screenshot', true );
    $design = get_post_meta( $post_id, '_mph_design_screenshot', true );
    return ! empty( $html ) && ! empty( $design );
}

/**
 * Public API: render the slider markup. Echoes nothing — returns a string.
 *
 * @param int   $post_id Post to render for.
 * @param array $args    variant ('thumb' | 'hero'), link (bool), browser_url (string), design_label (string).
 */
function mph_render_comparison_slider( $post_id, $args = array() ) {
    if ( class_exists( 'MPH_CS_Renderer' ) ) {
        return MPH_CS_Renderer::render( $post_id, $args );
    }
    return '';
}
