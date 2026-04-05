<?php
$selected_terms = get_post_meta( $post->ID, '_ctib_selected_terms', true ) ?: array();
$all_terms = get_posts( array( 'post_type' => 'ctib_term', 'posts_per_page' => -1 ) );
?>

<div class="ctib-terms-manager">
    <p>Select items to include in this quotation from your Master Data library.</p>
    
    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 30px;">
        <div class="ctib-term-group">
            <h4><span class="dashicons dashicons-yes-alt" style="color:green;"></span> Inclusions / Included Services</h4>
            <div class="ctib-terms-list" style="max-height: 200px; overflow-y: auto; border: 1px solid #ddd; padding: 10px; background: #fff;">
                <?php foreach ( $all_terms as $term ) : ?>
                    <label style="display: block; margin-bottom: 5px;">
                        <input type="checkbox" name="ctib_selected_terms[]" value="<?php echo $term->ID; ?>" <?php checked( in_array( $term->ID, $selected_terms ) ); ?>>
                        <?php echo esc_html( $term->post_title ); ?>
                    </label>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="ctib-custom-notes">
            <h4><span class="dashicons dashicons-edit"></span> Custom Notes / Exclusions</h4>
            <textarea name="ctib_custom_exclusions" style="width: 100%; height: 200px;" placeholder="Add unique exclusions or special notes for this specific client..."><?php echo esc_textarea( get_post_meta( $post->ID, '_ctib_custom_exclusions', true ) ); ?></textarea>
        </div>
    </div>
</div>