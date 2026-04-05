jQuery(document).ready(function($) {
    
    // PDF Generation via Browser Print Engine
    // The @media print CSS ensures it formats cleanly like a PDF document
    $('#ctib-download-pdf').on('click', function(e) {
        e.preventDefault();
        window.print();
    });

    // Tracking clicks for Analytics (Optional enhancement)
    $('.ctib-btn-whatsapp').on('click', function() {
        console.log("Client clicked Accept via WhatsApp.");
        // Here you could trigger an AJAX call to update the status to "Viewed / Accepted"
    });
});