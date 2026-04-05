<?php
class CTIB_Frontend {

    public static function init() {
        add_shortcode( 'ctib_agent_dashboard', array( __CLASS__, 'render_agent_dashboard' ) );
        add_action( 'wp_enqueue_scripts', array( __CLASS__, 'enqueue_frontend_scripts' ) );
    }

    public static function enqueue_frontend_scripts() {
        global $post;
        // Only load heavy scripts if the shortcode is on the page
        if ( is_a( $post, 'WP_Post' ) && has_shortcode( $post->post_content, 'ctib_agent_dashboard' ) ) {
            wp_enqueue_style( 'ctib-frontend-dash-css', CTIB_PLUGIN_URL . 'public/css/ctib-frontend-dash.css', array(), CTIB_VERSION );
        }
    }

    public static function render_agent_dashboard( $atts ) {
        // Security Check: Ensure the user is logged in
        if ( ! is_user_logged_in() ) {
            return '<div class="ctib-alert ctib-error">You must be logged in as an agent to view this dashboard. <a href="' . wp_login_url() . '">Login here</a>.</div>';
        }

        // Check if user has permission (e.g., they are an Administrator, Editor, or custom 'Travel Agent' role)
        if ( ! current_user_can( 'edit_posts' ) ) {
            return '<div class="ctib-alert ctib-error">You do not have permission to manage quotations.</div>';
        }

        ob_start();
        self::display_dashboard_html();
        return ob_get_clean();
    }

    private static function display_dashboard_html() {
        // Fetch the 10 most recent quotes
        $args = array(
            'post_type'      => 'itinerary',
            'posts_per_page' => 10,
            'post_status'    => 'any',
        );
        $quotes = new WP_Query( $args );
        ?>
        <div class="ctib-frontend-wrapper">
            <div class="ctib-dash-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                <h2>Agent Portal: Recent Quotations</h2>
                <a href="?action=new_quote" class="ctib-btn ctib-btn-primary">+ Build New Quote</a>
            </div>

            <table class="ctib-table" style="width: 100%; border-collapse: collapse; text-align: left;">
                <thead>
                    <tr style="background: #f1f1f1;">
                        <th style="padding: 10px; border: 1px solid #ddd;">Client / Itinerary Name</th>
                        <th style="padding: 10px; border: 1px solid #ddd;">Date Created</th>
                        <th style="padding: 10px; border: 1px solid #ddd;">Status</th>
                        <th style="padding: 10px; border: 1px solid #ddd;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ( $quotes->have_posts() ) : while ( $quotes->have_posts() ) : $quotes->the_post(); 
                        $status = get_post_meta( get_the_ID(), '_ctib_quote_status', true ) ?: 'Draft';
                    ?>
                        <tr>
                            <td style="padding: 10px; border: 1px solid #ddd;">
                                <strong><?php the_title(); ?></strong><br>
                                <small><?php echo esc_html( get_post_meta( get_the_ID(), '_ctib_client_name', true ) ); ?></small>
                            </td>
                            <td style="padding: 10px; border: 1px solid #ddd;"><?php echo get_the_date(); ?></td>
                            <td style="padding: 10px; border: 1px solid #ddd;">
                                <span class="ctib-badge status-<?php echo strtolower($status); ?>"><?php echo esc_html($status); ?></span>
                            </td>
                            <td style="padding: 10px; border: 1px solid #ddd;">
                                <a href="?action=edit_quote&id=<?php echo get_the_ID(); ?>" class="ctib-action-link">Edit</a> | 
                                <a href="<?php the_permalink(); ?>" target="_blank" class="ctib-action-link">View Web Link</a>
                            </td>
                        </tr>
                    <?php endwhile; wp_reset_postdata(); else : ?>
                        <tr><td colspan="4" style="padding: 10px; border: 1px solid #ddd;">No quotes found. Start building!</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <?php
    }
}