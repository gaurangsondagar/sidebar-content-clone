jQuery(document).ready(function($) {
    // Create the cloner container with translatable strings
    const clonerContainer = $(
        '<div id="widget-cloner-container" class="widget-cloner-container">' +
        '<h3>' + SidebarCloneData.i18n.sidebarCloneTitle + '</h3>' +
        '<div class="widget-cloner-fields">' +
        '<label for="source-widget-area">' + SidebarCloneData.i18n.sourceLabel + '</label>' +
        '<select id="source-widget-area" class="widget-area-select">' +
        '<option value="">' + SidebarCloneData.i18n.selectOption + '</option>' +
        '</select>' +
        '<label for="destination-widget-area">' + SidebarCloneData.i18n.destinationLabel + '</label>' +
        '<select id="destination-widget-area" class="widget-area-select">' +
        '<option value="">' + SidebarCloneData.i18n.selectOption + '</option>' +
        '</select>' +
        '<button id="clone-widgets-btn" class="button button-primary">' + SidebarCloneData.i18n.cloneButton + '</button>' +
        '<span id="clone-status"></span>' +
        '</div>' +
        '</div>'
    );

    // Insert the UI after a short delay
    setTimeout(function () {
        $('.interface-interface-skeleton__body').before(clonerContainer);
        populateWidgetAreas();
    }, 500);

    // Populate dropdowns with widget areas
    function populateWidgetAreas() {
        const sourceSelect = $('#source-widget-area');
        const destSelect = $('#destination-widget-area');

        $.each(SidebarCloneData.sidebars, function(id, name) {
            const option = $('<option>', { value: id, text: name });
            sourceSelect.append(option.clone());
            destSelect.append(option);
        });
    }

    // Clone widgets on button click
    $(document).on('click', '#clone-widgets-btn', function() {
        const source = $('#source-widget-area').val();
        const destination = $('#destination-widget-area').val();
        const statusElement = $('#clone-status');

        if (!source || !destination) {
            statusElement.text(SidebarCloneData.i18n.selectBoth).css('color', 'red');
            return;
        }

        if (source === destination) {
            statusElement.text(SidebarCloneData.i18n.sameSourceDestination).css('color', 'red');
            return;
        }

        statusElement.text(SidebarCloneData.i18n.cloning).css('color', 'blue');

        $.ajax({
            url: SidebarCloneData.ajaxurl,
            type: 'POST',
            data: {
                action: 'clone_widget_area',
                source: source,
                destination: destination,
                nonce: SidebarCloneData.nonce
            },
            success: function(response) {
                if (response.success) {
                    statusElement.text(response.data).css('color', 'green');
                    setTimeout(function() {
                        location.reload();
                    }, 1500);
                } else {
                    statusElement.text(response.data).css('color', 'red');
                }
            },
            error: function(xhr, status, error) {
                statusElement.text(SidebarCloneData.i18n.ajaxError + error).css('color', 'red');
            }
        });
    });
});
