<?php
class CTIB_Activator {
    public static function activate() {
        CTIB_CPT::register();
        flush_rewrite_rules();
    }
}