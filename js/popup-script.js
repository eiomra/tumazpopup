jQuery(document).ready(function($) {
    var popups = $('#pm-popup-container .pm-popup');
    var currentIndex = 0;

    // Function to generate a random interval between min and max (in milliseconds)
    function getRandomInterval(min, max) {
        return Math.floor(Math.random() * (max - min + 1)) + min;
    }

    // Show a popup with a random interval between pop-ups
    function showPopup(index) {
        if (popups.length === 0) return;

        var popup = popups.eq(index);
        popup.fadeIn();

        var displayDuration = getRandomInterval(3000, 6000); // Duration to display the popup (3 to 6 seconds)
        var intervalDuration = getRandomInterval(8000, 10000); // Interval between pop-ups (1 to 3 seconds)

        setTimeout(function() {
            popup.fadeOut();
            currentIndex = (index + 1) % popups.length;
            setTimeout(function() {
                showPopup(currentIndex);
            }, intervalDuration);
        }, displayDuration);
    }

    if (popups.length > 0) {
        showPopup(currentIndex);
    }

    $('#pm-popup-container').on('click', '.pm-close', function() {
        $(this).closest('.pm-popup').fadeOut();
    });

    $(window).on('click', function(event) {
        if ($(event.target).is('.pm-popup')) {
            $(event.target).fadeOut();
        }
    });
});
