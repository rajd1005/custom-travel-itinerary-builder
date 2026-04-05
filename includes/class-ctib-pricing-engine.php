<?php
class CTIB_Pricing_Engine {

    /**
     * Calculate the exact mathematical breakdown of a quotation.
     * * @param int $adults Number of adults
     * @param float $rate_adult Cost per adult
     * @param int $children Number of children
     * @param float $rate_child Cost per child
     * @param array $discounts Array of discount arrays: [['type' => 'flat', 'val' => 2000], ['type' => 'percent', 'val' => 5]]
     * @param float $gst_rate The GST rate (e.g., 0.05 for 5%)
     * @return array Full pricing breakdown
     */
    public static function calculate_advanced( $adults, $rate_adult, $children, $rate_child, $discounts = array(), $gst_rate = 0.05 ) {
        
        // 1. Base Costs
        $adult_total = floatval($adults) * floatval($rate_adult);
        $child_total = floatval($children) * floatval($rate_child);
        $gross_base  = $adult_total + $child_total;

        // 2. Process Multiple Discounts (Stacking)
        $total_discount_amount = 0;
        $current_subtotal = $gross_base;

        if ( !empty($discounts) && is_array($discounts) ) {
            foreach ( $discounts as $discount ) {
                $discount_val = floatval($discount['val']);
                if ( $discount['type'] === 'flat' ) {
                    $total_discount_amount += $discount_val;
                    $current_subtotal -= $discount_val;
                } elseif ( $discount['type'] === 'percent' ) {
                    $calc_discount = $current_subtotal * ( $discount_val / 100 );
                    $total_discount_amount += $calc_discount;
                    $current_subtotal -= $calc_discount;
                }
            }
        }

        // Prevent negative totals
        if ( $current_subtotal < 0 ) {
            $current_subtotal = 0;
            $total_discount_amount = $gross_base;
        }

        // 3. Tax Calculation (GST applied AFTER discounts)
        $taxable_value = $current_subtotal;
        $total_tax     = $taxable_value * $gst_rate;
        $cgst          = $total_tax / 2;
        $sgst          = $total_tax / 2;

        // 4. Grand Total
        $grand_total = $taxable_value + $total_tax;

        return array(
            'adult_total'      => round($adult_total, 2),
            'child_total'      => round($child_total, 2),
            'gross_base'       => round($gross_base, 2),
            'total_discount'   => round($total_discount_amount, 2),
            'taxable_value'    => round($taxable_value, 2),
            'cgst'             => round($cgst, 2),
            'sgst'             => round($sgst, 2),
            'total_tax'        => round($total_tax, 2),
            'grand_total'      => round($grand_total, 2)
        );
    }
}