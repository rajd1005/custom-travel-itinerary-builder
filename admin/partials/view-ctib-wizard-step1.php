<?php
$client_name = get_post_meta( $post->ID, '_ctib_client_name', true );
$adults = get_post_meta( $post->ID, '_ctib_pax_adults', true ) ?: 2;
$children = get_post_meta( $post->ID, '_ctib_pax_children', true ) ?: 0;
?>
<div class="ctib-row">
    <label>Client Name:</label>
    <input type="text" name="ctib_client_name" value="<?php echo esc_attr( $client_name ); ?>" style="width:60%;">
</div>
<div class="ctib-row">
    <label>Adults:</label>
    <input type="number" id="ctib_pax_adults" name="ctib_pax_adults" value="<?php echo esc_attr( $adults ); ?>" min="1" style="width:80px;">
    <label style="margin-left:20px; width:auto;">Children:</label>
    <input type="number" id="ctib_pax_children" name="ctib_pax_children" value="<?php echo esc_attr( $children ); ?>" min="0" style="width:80px;">
</div>