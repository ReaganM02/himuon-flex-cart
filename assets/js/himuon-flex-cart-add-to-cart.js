jQuery(function ($) {
    if (typeof wc_add_to_cart_params === 'undefined' || !wc_add_to_cart_params.wc_ajax_url) return

    $(document).on('submit', 'form.cart', function (event) {
        const $form = $(this)
        if ($form.closest('.himuon-flex-cart-plugin').length) {
            return
        }

        const $button = $form.find('.single_add_to_cart_button, button[type="submit"]').first()
        if (!$button.length || $button.hasClass('disabled')) {
            return
        }

        event.preventDefault()

        $button.addClass('loading').prop('disabled', true)

        const dataArray = $form.serializeArray()
        const hasProductId = dataArray.some(function (field) {
            return field.name === 'product_id'
        })

        const hasButtonValue = $button.length && String($button.val() || '').trim() !== ''

        if (!hasProductId && hasButtonValue) {
            const buttonValue = String($button.val() || '').trim()
            dataArray.push({ name: 'product_id', value: buttonValue })
        }

        const data = $.param(dataArray)
        $(document.body).trigger('adding_to_cart', [$button, data])

        $.ajax({
            type: 'POST',
            url: wc_add_to_cart_params.wc_ajax_url.replace(
                '%%endpoint%%',
                'add_to_cart'
            ),
            data: data,
            dataType: 'json'
        }).done(function (response) {
            if (!response) {
                return
            }
            $(document.body).trigger('added_to_cart', [response.fragments || {}, response.cart_hash || '', $button])
        }).always(function () {
            $button.removeClass('loading').prop('disabled', false)
        })
    })
})
