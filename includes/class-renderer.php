<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class MPH_CS_Renderer {

    private static $enqueued = false;

    public function __construct() {
        add_action( 'wp_enqueue_scripts', array( $this, 'register_assets' ) );
        add_shortcode( 'mph_comparison_slider', array( $this, 'shortcode' ) );
    }

    public function register_assets() {
        wp_register_style(
            'mph-cs',
            MPH_CS_URL . 'assets/css/comparison-slider.css',
            array(),
            MPH_CS_VERSION
        );
        wp_register_script(
            'mph-cs',
            MPH_CS_URL . 'assets/js/comparison-slider.js',
            array(),
            MPH_CS_VERSION,
            true
        );
    }

    public static function render( $post_id, $args = array() ) {
        $post_id = absint( $post_id );
        if ( ! $post_id || ! mph_has_comparison_screenshots( $post_id ) ) {
            return '';
        }

        $defaults = array(
            'variant'      => 'thumb', // thumb | hero
            'link'         => true,    // navigate on tap (only when not dragging)
            'browser_url'  => '',      // override URL pill text
            'design_label' => '',      // override design-tool label
        );
        $args = wp_parse_args( $args, $defaults );

        $html_id   = (int) get_post_meta( $post_id, '_mph_html_screenshot', true );
        $design_id = (int) get_post_meta( $post_id, '_mph_design_screenshot', true );

        $html_url   = wp_get_attachment_image_url( $html_id, 'large' );
        $design_url = wp_get_attachment_image_url( $design_id, 'large' );
        if ( ! $html_url || ! $design_url ) {
            return '';
        }

        $browser_url  = self::resolve_browser_url( $post_id, $args['browser_url'] );
        $design_label = $args['design_label'];
        if ( ! $design_label ) {
            $design_label = get_post_meta( $post_id, '_mph_design_label', true );
        }
        if ( ! $design_label ) {
            $design_label = __( 'Design', 'mph-cs' );
        }

        $permalink = $args['link'] ? get_permalink( $post_id ) : '';
        $title     = get_the_title( $post_id );

        self::ensure_assets();

        ob_start();
        ?>
        <div
            class="mph-cs mph-cs--<?php echo esc_attr( $args['variant'] ); ?>"
            data-mph-cs
            <?php if ( $permalink ) : ?>data-permalink="<?php echo esc_attr( $permalink ); ?>"<?php endif; ?>
        >
            <div class="mph-cs__stage">

                <div class="mph-cs__layer mph-cs__layer--back">
                    <div class="mph-cs__chrome mph-cs__chrome--browser">
                        <div class="mph-cs__browser-dots" aria-hidden="true">
                            <span class="mph-cs__dot mph-cs__dot--red"></span>
                            <span class="mph-cs__dot mph-cs__dot--yellow"></span>
                            <span class="mph-cs__dot mph-cs__dot--green"></span>
                        </div>
                        <div class="mph-cs__url">
                            <span class="mph-cs__url-lock" aria-hidden="true">
                                <svg viewBox="0 0 16 16" width="10" height="10" fill="currentColor"><path d="M8 1a3 3 0 0 0-3 3v3H4a1 1 0 0 0-1 1v6a1 1 0 0 0 1 1h8a1 1 0 0 0 1-1V8a1 1 0 0 0-1-1h-1V4a3 3 0 0 0-3-3zm2 6H6V4a2 2 0 1 1 4 0v3z"/></svg>
                            </span>
                            <span class="mph-cs__url-text"><?php echo esc_html( $browser_url ); ?></span>
                        </div>
                    </div>
                    <div class="mph-cs__canvas">
                        <img
                            src="<?php echo esc_url( $html_url ); ?>"
                            alt="<?php echo esc_attr( sprintf( /* translators: %s: project title */ __( '%s — built site', 'mph-cs' ), $title ) ); ?>"
                            class="mph-cs__img"
                            draggable="false"
                            loading="lazy"
                        >
                    </div>
                </div>

                <div class="mph-cs__layer mph-cs__layer--front">
                    <div class="mph-cs__chrome mph-cs__chrome--design">
                        <span class="mph-cs__design-mark" aria-hidden="true">
                            <svg viewBox="0 0 16 16" width="14" height="14" fill="currentColor"><path d="M8 0a8 8 0 1 0 0 16A8 8 0 0 0 8 0zm0 2.5a5.5 5.5 0 1 1 0 11 5.5 5.5 0 0 1 0-11zM5 5a1 1 0 1 0 0 2 1 1 0 0 0 0-2zm6 0a1 1 0 1 0 0 2 1 1 0 0 0 0-2zM4.6 9.4a.75.75 0 0 1 1.05.15 3 3 0 0 0 4.7 0 .75.75 0 0 1 1.2.9 4.5 4.5 0 0 1-7.1 0 .75.75 0 0 1 .15-1.05z"/></svg>
                        </span>
                        <div class="mph-cs__design-title"><?php echo esc_html( $design_label ); ?> &mdash; <?php echo esc_html( $title ); ?></div>
                        <div class="mph-cs__design-tabs" aria-hidden="true">
                            <span class="mph-cs__design-tab is-active"><?php esc_html_e( 'Design', 'mph-cs' ); ?></span>
                            <span class="mph-cs__design-tab"><?php esc_html_e( 'Prototype', 'mph-cs' ); ?></span>
                        </div>
                    </div>
                    <div class="mph-cs__canvas">
                        <img
                            src="<?php echo esc_url( $design_url ); ?>"
                            alt="<?php echo esc_attr( sprintf( /* translators: %s: project title */ __( '%s — design source', 'mph-cs' ), $title ) ); ?>"
                            class="mph-cs__img"
                            draggable="false"
                            loading="lazy"
                        >
                    </div>
                </div>

                <div class="mph-cs__labels" aria-hidden="true">
                    <span class="mph-cs__label mph-cs__label--left"><?php echo esc_html( strtoupper( $design_label ) ); ?></span>
                    <span class="mph-cs__label mph-cs__label--right">HTML</span>
                </div>

                <button
                    type="button"
                    class="mph-cs__handle"
                    aria-label="<?php esc_attr_e( 'Drag to compare design and build', 'mph-cs' ); ?>"
                    aria-valuemin="0"
                    aria-valuemax="100"
                    aria-valuenow="50"
                    role="slider"
                >
                    <span class="mph-cs__handle-bar" aria-hidden="true"></span>
                    <span class="mph-cs__handle-grip" aria-hidden="true">
                        <svg viewBox="0 0 16 16" width="14" height="14" fill="currentColor"><path d="M6 3 2 8l4 5V3zm4 0v10l4-5-4-5z"/></svg>
                    </span>
                </button>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }

    public function shortcode( $atts ) {
        $atts = shortcode_atts(
            array(
                'id'      => 0,
                'variant' => 'thumb',
                'link'    => 'yes',
            ),
            $atts,
            'mph_comparison_slider'
        );
        return self::render(
            (int) $atts['id'],
            array(
                'variant' => sanitize_key( $atts['variant'] ),
                'link'    => in_array( strtolower( (string) $atts['link'] ), array( 'yes', 'true', '1' ), true ),
            )
        );
    }

    private static function ensure_assets() {
        if ( self::$enqueued ) {
            return;
        }
        wp_enqueue_style( 'mph-cs' );
        wp_enqueue_script( 'mph-cs' );
        self::$enqueued = true;
    }

    private static function resolve_browser_url( $post_id, $override ) {
        if ( $override ) {
            return $override;
        }
        $project_url = get_post_meta( $post_id, 'project_url', true );
        if ( $project_url ) {
            $parts = wp_parse_url( $project_url );
            $host  = isset( $parts['host'] ) ? $parts['host'] : '';
            $path  = isset( $parts['path'] ) ? $parts['path'] : '';
            $url   = trim( $host . $path, '/' );
            if ( $url ) {
                return $url;
            }
        }
        $host = wp_parse_url( home_url(), PHP_URL_HOST );
        $slug = get_post_field( 'post_name', $post_id );
        return $host . '/' . $slug;
    }
}
