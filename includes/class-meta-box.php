<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class MPH_CS_Meta_Box {

    public function __construct() {
        add_action( 'add_meta_boxes', array( $this, 'register' ) );
        add_action( 'save_post_' . MPH_CS_POST_TYPE, array( $this, 'save' ) );
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue' ) );
    }

    public function register() {
        add_meta_box(
            'mph_cs_screenshots',
            __( 'Comparison Screenshots', 'mph-cs' ),
            array( $this, 'render' ),
            MPH_CS_POST_TYPE,
            'normal',
            'high'
        );
    }

    public function render( $post ) {
        wp_nonce_field( 'mph_cs_save', 'mph_cs_nonce' );

        $html_id      = get_post_meta( $post->ID, '_mph_html_screenshot', true );
        $design_id    = get_post_meta( $post->ID, '_mph_design_screenshot', true );
        $design_label = get_post_meta( $post->ID, '_mph_design_label', true );

        echo '<p class="description" style="margin-bottom:14px;">';
        esc_html_e( 'Upload a screenshot of the design source (Figma, Sketch, XD, etc.) and the actual built HTML to enable the drag-to-compare slider on this project.', 'mph-cs' );
        echo '</p>';

        echo '<div style="display:flex; gap:24px; flex-wrap:wrap;">';
        $this->render_picker(
            'design',
            __( 'Design Screenshot', 'mph-cs' ),
            $design_id,
            __( 'Appears on the LEFT side of the slider, with design-tool toolbar decoration.', 'mph-cs' )
        );
        $this->render_picker(
            'html',
            __( 'HTML Screenshot (Build)', 'mph-cs' ),
            $html_id,
            __( 'Appears on the RIGHT side of the slider, with browser-chrome decoration.', 'mph-cs' )
        );
        echo '</div>';

        ?>
        <p style="margin-top:18px;">
            <label for="mph_cs_design_label"><strong><?php esc_html_e( 'Design Tool Label', 'mph-cs' ); ?></strong></label><br>
            <input
                type="text"
                id="mph_cs_design_label"
                name="mph_cs_design_label"
                value="<?php echo esc_attr( $design_label ); ?>"
                placeholder="<?php esc_attr_e( 'Design', 'mph-cs' ); ?>"
                class="regular-text"
            >
            <span class="description" style="display:block; margin-top:4px;">
                <?php esc_html_e( 'Optional. Shown in the design-side toolbar (e.g. "Figma", "Sketch", "Adobe XD"). Defaults to "Design".', 'mph-cs' ); ?>
            </span>
        </p>
        <?php
    }

    private function render_picker( $key, $label, $value, $desc ) {
        $img_src = $value ? wp_get_attachment_image_url( (int) $value, 'medium' ) : '';
        $input_id = 'mph_cs_' . $key;
        ?>
        <div class="mph-cs-field" style="flex:1 1 280px; min-width:260px;">
            <p style="margin:0 0 4px;"><strong><?php echo esc_html( $label ); ?></strong></p>
            <p class="description" style="margin:0 0 8px;"><?php echo esc_html( $desc ); ?></p>
            <input type="hidden" name="<?php echo esc_attr( $input_id ); ?>" id="<?php echo esc_attr( $input_id ); ?>" value="<?php echo esc_attr( $value ); ?>">
            <div
                class="mph-cs-preview"
                id="<?php echo esc_attr( $input_id ); ?>_preview"
                style="margin:0 0 8px; padding:8px; background:#f6f7f7; border:1px dashed #c3c4c7; min-height:80px; display:flex; align-items:center; justify-content:center;"
            >
                <?php if ( $img_src ) : ?>
                    <img src="<?php echo esc_url( $img_src ); ?>" alt="" style="max-width:100%; height:auto; max-height:200px;">
                <?php else : ?>
                    <span style="color:#8c8f94;"><?php esc_html_e( 'No image selected', 'mph-cs' ); ?></span>
                <?php endif; ?>
            </div>
            <button type="button" class="button mph-cs-select" data-target="<?php echo esc_attr( $input_id ); ?>"><?php esc_html_e( 'Select Image', 'mph-cs' ); ?></button>
            <button
                type="button"
                class="button mph-cs-remove"
                data-target="<?php echo esc_attr( $input_id ); ?>"
                <?php echo $value ? '' : ' style="display:none;"'; ?>
            ><?php esc_html_e( 'Remove', 'mph-cs' ); ?></button>
        </div>
        <?php
    }

    public function save( $post_id ) {
        if ( ! isset( $_POST['mph_cs_nonce'] ) ) {
            return;
        }
        if ( ! wp_verify_nonce( sanitize_key( wp_unslash( $_POST['mph_cs_nonce'] ) ), 'mph_cs_save' ) ) {
            return;
        }
        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
            return;
        }
        if ( ! current_user_can( 'edit_post', $post_id ) ) {
            return;
        }

        $attachments = array(
            'mph_cs_html'   => '_mph_html_screenshot',
            'mph_cs_design' => '_mph_design_screenshot',
        );
        foreach ( $attachments as $input => $meta ) {
            if ( isset( $_POST[ $input ] ) ) {
                $val = absint( $_POST[ $input ] );
                if ( $val ) {
                    update_post_meta( $post_id, $meta, $val );
                } else {
                    delete_post_meta( $post_id, $meta );
                }
            }
        }

        if ( isset( $_POST['mph_cs_design_label'] ) ) {
            $label = sanitize_text_field( wp_unslash( $_POST['mph_cs_design_label'] ) );
            if ( $label !== '' ) {
                update_post_meta( $post_id, '_mph_design_label', $label );
            } else {
                delete_post_meta( $post_id, '_mph_design_label' );
            }
        }
    }

    public function enqueue( $hook ) {
        if ( ! in_array( $hook, array( 'post.php', 'post-new.php' ), true ) ) {
            return;
        }
        $screen = get_current_screen();
        if ( ! $screen || $screen->post_type !== MPH_CS_POST_TYPE ) {
            return;
        }

        wp_enqueue_media();
        wp_enqueue_script(
            'mph-cs-admin',
            MPH_CS_URL . 'assets/js/admin-meta.js',
            array( 'jquery' ),
            MPH_CS_VERSION,
            true
        );
    }
}
