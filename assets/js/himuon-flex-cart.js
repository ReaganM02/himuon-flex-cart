jQuery(function ($) {
    function getSideCart() {
        return $('#himuon-side-cart')
    }

    function setLoading(isLoading) {
        var $sideCart = getSideCart()
        if (!$sideCart.length) {
            return
        }
        $sideCart.toggleClass('is-loading', !!isLoading)
    }

    function updateCartItem($qtyWrap, newQty) {
        if (typeof wc_cart_fragments_params === 'undefined' || !himuonFlexCart || !himuonFlexCart.nonce) {
            return
        }

        var cartItemKey = $qtyWrap.data('cart-item-key')
        if (!cartItemKey) {
            return
        }

        if ($qtyWrap.data('updating')) {
            return
        }

        $qtyWrap.data('updating', true)
        setLoading(true)
        console.log('hit')
        $.ajax({
            url: wc_cart_fragments_params.wc_ajax_url.toString().replace('%%endpoint%%', 'himuon_update_cart_item'),
            type: 'POST',
            data: {
                cartItemKey: cartItemKey,
                quantity: newQty,
                nonce: himuonFlexCart.nonce
            },
            complete: function () {
                $qtyWrap.data('updating', false)
                setLoading(false)
            },
            success: function (data) {
                var fragments = data && data.fragments ? data.fragments : (data && data.data ? data.data.fragments : null)
                if (fragments) {
                    $.each(fragments, function (key, value) {
                        $(key).replaceWith(value)
                    })
                    $(document.body).trigger('wc_fragments_refreshed')
                }
            }
        })
    }

    function refreshSideCart() {
        if (typeof wc_cart_fragments_params === 'undefined') {
            return
        }

        setLoading(true)
        $(document.body).trigger('wc_fragment_refresh')
    }

    $(document.body).on('added_to_cart removed_from_cart updated_cart_totals updated_wc_div', refreshSideCart)
    $(document.body).on('wc_fragments_refreshed', function () {
        setLoading(false)
    })

    setLoading(false)

    function debounce(fn, wait) {
        var timeoutId
        return function () {
            var context = this
            var args = arguments
            clearTimeout(timeoutId)
            timeoutId = setTimeout(function () {
                fn.apply(context, args)
            }, wait)
        }
    }

    var debouncedUpdate = debounce(async function (input) {
        var $wrap = $(input).closest('.himuon-cart--quantity')
        if (!$wrap.length) {
            return
        }
        var qty = parseInt(input.value, 10)
        if (Number.isNaN(qty)) {
            qty = 1
        }
        updateCartItem($wrap, qty)
    }, 300)

    function clampQuantity(input, nextValue) {
        const min = input.hasAttribute('min') ? parseInt(input.min, 10) : 1
        const max = input.hasAttribute('max') ? parseInt(input.max, 10) : Infinity
        const step = input.hasAttribute('step') ? parseInt(input.step, 10) : 1

        let value = parseInt(nextValue, 10)

        if (Number.isNaN(value)) {
            value = min
        }

        // Clamp
        value = Math.max(min, Math.min(max, value))

        // Normalize to step
        value = Math.round((value - min) / step) * step + min

        input.value = value
    }

    document.addEventListener('click', (e) => {
        const btn = e.target.closest('.himuon-cart--plus, .himuon-cart--minus')
        if (!btn) return

        const input = btn.closest('.himuon-cart--quantity').querySelector('.himuon-cart--qty')


        if (!input || input.disabled) return

        const current = parseInt(input.value, 10) || input.min || 1
        const step = parseInt(input.step, 10) || 1

        const next = btn.classList.contains('himuon-cart--plus')
            ? current + step
            : current - step

        clampQuantity(input, next)
        debouncedUpdate(input)
    })
})
