<?php
class CTIB_Frontend {

    public static function init() {
        add_shortcode( 'ctib_agent_dashboard', array( __CLASS__, 'render_agent_dashboard' ) );
        add_action( 'wp_enqueue_scripts', array( __CLASS__, 'enqueue_frontend_scripts' ) );
    }

    public static function enqueue_frontend_scripts() {
        global $post;
        if ( is_a( $post, 'WP_Post' ) && has_shortcode( $post->post_content, 'ctib_agent_dashboard' ) ) {
            wp_enqueue_style( 'ctib-frontend-dash-css', CTIB_PLUGIN_URL . 'public/css/ctib-frontend-dash.css', array(), CTIB_VERSION );
        }
    }

    public static function render_agent_dashboard( $atts ) {
        if ( ! is_user_logged_in() || ! current_user_can( 'edit_posts' ) ) {
            return '<div style="padding: 20px; background: #fee; color: #c00; border: 1px solid #c00; border-radius: 5px;">Access Denied. Agent login required.</div>';
        }

        // MAGIC FIX: If the user clicked "+ Build New Quote", load the creation form instead of the dashboard!
        if ( isset( $_GET['action'] ) && $_GET['action'] === 'new_quote' ) {
            return CTIB_Frontend_Manager::render_portal(); 
        }

        ob_start();
        self::display_dashboard_html();
        return ob_get_clean();
    }

    private static function display_dashboard_html() {
        $args = array( 'post_type' => 'itinerary', 'posts_per_page' => 15, 'post_status' => 'any' );
        $quotes = new WP_Query( $args );
        
        // Get current page URL to make the button work dynamically
        $current_url = get_permalink();
        $new_quote_url = add_query_arg( 'action', 'new_quote', $current_url );
        ?>
        <div class="ctib-frontend-wrapper" style="max-width: 1000px; margin: 0 auto; font-family: sans-serif;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; padding-bottom: 10px; border-bottom: 2px solid #eee;">
                <h2 style="margin:0;">Agent Portal: Recent Quotations</h2>
                <a href="<?php echo esc_url( $new_quote_url ); ?>" style="background: #0073aa; color: #fff; padding: 10px 20px; text-decoration: none; border-radius: 4px; font-weight: bold;">+ Build New Quote</a>
            </div>

            <table style="width: 100%; border-collapse: collapse; text-align: left; background: #fff; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
                <thead>
                    <tr style="background: #f8f9fa;">
                        <th style="padding: 12px; border: 1px solid #ddd;">Client Name</th>
                        <th style="padding: 12px; border: 1px solid #ddd;">Date</th>
                        <th style="padding: 12px; border: 1px solid #ddd;">Status</th>
                        <th style="padding: 12px; border: 1px solid #ddd;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ( $quotes->have_posts() ) : while ( $quotes->have_posts() ) : $quotes->the_post(); 
                        $status = get_post_meta( get_the_ID(), '_ctib_quote_status', true ) ?: 'Draft';
                    ?>
                        <tr>
                            <td style="padding: 12px; border: 1px solid #ddd;">
                                <strong><?php echo esc_html( get_post_meta( get_the_ID(), '_ctib_client_name', true ) ); ?></strong><br>
                                <small><?php the_title(); ?></small>
                            </td>
                            <td style="padding: 12px; border: 1px solid #ddd;"><?php echo get_the_date(); ?></td>
                            <td style="padding: 12px; border: 1px solid #ddd;">
                                <span style="background: #e2e8f0; padding: 4px 8px; border-radius: 12px; font-size: 12px;"><?php echo esc_html($status); ?></span>
                            </td>
                            <td style="padding: 12px; border: 1px solid #ddd;">
                                <a href="<?php echo admin_url('post.php?post=' . get_the_ID() . '&action=edit'); ?>" target="_blank" style="color: #0073aa; text-decoration: none;">Edit Backend</a> | 
                                <a href="<?php echo get_permalink(); ?>" target="_blank" style="color: #25D366; text-decoration: none; font-weight:bold;">View Magic Link</a>
                            </td>
                        </tr>
                    <?php endwhile; wp_reset_postdata(); else : ?>
                        <tr><td colspan="4" style="padding: 20px; text-align: center; border: 1px solid #ddd;">No quotes found. Start building!</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <?php
    }
}