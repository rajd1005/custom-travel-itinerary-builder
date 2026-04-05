<?php
$cost_a = get_post_meta( $post->ID, '_ctib_cost_adult', true ) ?: 0;
$cost_c = get_post_meta( $post->ID, '_ctib_cost_child', true ) ?: 0;
?>
<div class="ctib-grid">
    <div>
        <div class="ctib-row">
            <label>Cost Per Adult (₹):</label>
            <input type="number" id="ctib_cost_adult" name="ctib_cost_adult" value="<?php echo esc_attr($cost_a); ?>">
        </div>
        <div class="ctib-row">
            <label>Cost Per Child (₹):</label>
            <input type="number" id="ctib_cost_child" name="ctib_cost_child" value="<?php echo esc_attr($cost_c); ?>">
        </div>
    </div>
    <div class="ctib-preview">
        <h4>Live Preview</h4>
        <p>Base Total: <strong id="ctib-prev-base">₹ 0.00</strong></p>
        <p>GST (5%): <strong id="ctib-prev-tax">₹ 0.00</strong></p>
        <hr>
        <h3>Grand Total: <strong id="ctib-prev-total" style="color: green;">₹ 0.00</strong></h3>
    </div>
</div>