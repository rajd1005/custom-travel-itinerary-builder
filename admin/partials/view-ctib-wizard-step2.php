<?php 
$days = get_post_meta( $post->ID, '_ctib_days', true ) ?: array(); 
$destinations = get_terms( array( 'taxonomy' => 'ctib_destination', 'hide_empty' => false ) );
wp_nonce_field( 'ctib_admin_action', 'ctib_admin_nonce' );
?>

<div id="ctib-itinerary-app">
    <div id="ctib-days-wrapper" class="sortable-container">
        <?php if ( empty( $days ) ) : ?>
            <?php render_day_row( 1, array(), $destinations ); ?>
        <?php else : foreach ( $days as $index => $day ) : ?>
            <?php render_day_row( $index + 1, $day, $destinations ); ?>
        <?php endforeach; endif; ?>
    </div>
    
    <div style="margin-top: 20px; padding: 15px; background: #fff; border: 1px dashed #ccc;">
        <button type="button" id="ctib-add-day" class="button button-primary">+ Add New Day</button>
        <span style="margin-left: 10px; color: #666;">Tip: Drag and drop boxes to reorder days.</span>
    </div>
</div>

<?php
/**
 * Helper function to render a single day row
 */
function render_day_row( $number, $data, $destinations ) {
    $title = $data['title'] ?? '';
    $desc  = $data['desc'] ?? '';
    $hotel = $data['hotel_id'] ?? '';
    $meals = $data['meals'] ?? array();
    ?>
    <div class="ctib-day-box" data-day="<?php echo $number; ?>">
        <div class="ctib-day-header">
            <span class="dashicons dashicons-menu drag-handle"></span>
            <strong>Day <?php echo $number; ?></strong>
            <button type="button" class="ctib-remove-day">×</button>
        </div>

        <div class="ctib-day-body">
            <input type="text" name="ctib_day_title[]" value="<?php echo esc_attr($title); ?>" placeholder="Main Activity Title" class="widefat">
            
            <div class="ctib-row-split">
                <div class="ctib-col">
                    <label>Description:</label>
                    <textarea name="ctib_day_desc[]" rows="3" class="widefat"><?php echo esc_textarea($desc); ?></textarea>
                </div>
                <div class="ctib-col">
                    <label>Destination (Filter):</label>
                    <select class="ctib-dest-filter widefat">
                        <option value="">Select Destination...</option>
                        <?php foreach($destinations as $dest) echo '<option value="'.$dest->term_id.'">'.$dest->name.'</option>'; ?>
                    </select>

                    <label style="margin-top:10px; display:block;">Select Hotel:</label>
                    <select name="ctib_day_hotel_id[]" class="ctib-hotel-select widefat">
                        <option value="">-- Choose Hotel --</option>
                        </select>
                </div>
            </div>

            <div class="ctib-day-footer">
                <div class="ctib-meals">
                    <label><input type="checkbox" name="ctib_meals[<?php echo $number-1; ?>][]" value="B" <?php checked(in_array('B', $meals)); ?>> Breakfast</label>
                    <label><input type="checkbox" name="ctib_meals[<?php echo $number-1; ?>][]" value="L" <?php checked(in_array('L', $meals)); ?>> Lunch</label>
                    <label><input type="checkbox" name="ctib_meals[<?php echo $number-1; ?>][]" value="D" <?php checked(in_array('D', $meals)); ?>> Dinner</label>
                </div>
                <div class="ctib-hotel-preview">
                    </div>
            </div>
        </div>
    </div>
    <?php
}