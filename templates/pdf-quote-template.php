<?php
// This acts as a basic HTML structure that a library like DomPDF or mPDF would consume.
?>
<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: sans-serif; color: #333; }
        h1 { color: #0073aa; }
        .table { width: 100%; border-collapse: collapse; }
        .table th, .table td { border: 1px solid #000; padding: 8px; }
    </style>
</head>
<body>
    <h1><?php echo esc_html( get_the_title( $post_id ) ); ?></h1>
    <p>Client: <?php echo esc_html( get_post_meta( $post_id, '_ctib_client_name', true ) ); ?></p>
    <hr>
    <p>Please view the live link for full details.</p>
</body>
</html>