<?php
// Accessed via output buffering in the AJAX class
$client = get_post_meta( $post_id, '_ctib_client_name', true );
$url = get_permalink( $post_id );
?>
<div style="font-family: Arial, sans-serif; padding: 20px;">
    <h2>Hello <?php echo esc_html( $client ); ?>,</h2>
    <p>Your custom travel itinerary has been prepared and is ready for your review.</p>
    <p><a href="<?php echo esc_url( $url ); ?>" style="padding:10px 20px; background:#0073aa; color:#fff; text-decoration:none; border-radius:5px;">View Your Itinerary & Quotation</a></p>
    <p>Please let us know if you require any changes.</p>
    <p>Warm Regards,<br>Your Travel Agency</p>
</div>