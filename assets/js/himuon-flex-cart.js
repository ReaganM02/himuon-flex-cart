jQuery(function ($) {

    /**
     * =============================================================================
     * Side Cart State + Helpers
     * =============================================================================
     */
    var isUpdatingCartItem = false

    const getSideCart = () => { return $('#himuon-side-cart') }

    const setSideCartLoading = (isLoading) => {
        const $sideCart = getSideCart()
        if (!$sideCart.length) {
            return
        }
        $sideCart.toggleClass('is-loading', !!isLoading)
    }

    const setCartItemVariationLoading = (isLoading) => {
        const $el = $('.himuon-cart--spinner-wrapper')
        if ($el.length) {
            $el.toggleClass('himuon-cart--show-variation-spinner', !!isLoading)
        }
    }

    const refreshSideCart = () => {
        if (typeof wc_cart_fragments_params === 'undefined') {
            return
        }
        if (isUpdatingCartItem) {
            return
        }

        setSideCartLoading(true)
        $(document.body).trigger('wc_fragment_refresh')
    }

    const debounce = (fn, wait) => {
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

    /**
     * =============================================================================
     * Cart Item Quantity Updates
     * =============================================================================
     */

    const updateCartItemQuantity = ($qtyWrap, newQty) => {
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
        isUpdatingCartItem = true
        setSideCartLoading(true)
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
                isUpdatingCartItem = false
                setSideCartLoading(false)
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

    const debounceCartItemQuantityUpdate = debounce(async function (input) {
        var $wrap = $(input).closest('.himuon-cart--quantity')
        if (!$wrap.length) {
            return
        }
        var qty = parseInt(input.value, 10)
        if (Number.isNaN(qty)) {
            qty = 1
        }
        updateCartItemQuantity($wrap, qty)
    }, 300)

    const clampQuantity = (input, nextValue) => {
        const min = input.hasAttribute('min') ? parseInt(input.min, 10) : 1
        const max = input.hasAttribute('max') ? parseInt(input.max, 10) : Infinity
        const step = input.hasAttribute('step') ? parseInt(input.step, 10) : 1

        let value = parseInt(nextValue, 10)

        if (Number.isNaN(value)) {
            value = min
        }
        value = Math.max(min, Math.min(max, value))
        value = Math.round((value - min) / step) * step + min

        input.value = value
    }


    const updateQuantityButtons = (qtyWrap, input) => {
        if (!qtyWrap || !input) {
            return
        }
        const min = input.hasAttribute('min') ? parseInt(input.min, 10) : 1
        const max = input.hasAttribute('max') ? parseInt(input.max, 10) : Infinity
        const current = parseInt(input.value, 10)
        const isAtMin = !Number.isNaN(current) && current <= min
        const isAtMax = !Number.isNaN(current) && current >= max

        const minusBtn = qtyWrap.querySelector('.himuon-cart--minus')
        const plusBtn = qtyWrap.querySelector('.himuon-cart--plus')

        if (minusBtn) {
            minusBtn.disabled = isAtMin
        }
        if (plusBtn) {
            plusBtn.disabled = isAtMax
        }
    }


    /**
     * =============================================================================
     * Variation Form: Render + Init
     * =============================================================================
     */

    const renderVariationForm = (productId, attributes, qty) => {
        if (typeof wc_cart_fragments_params === 'undefined' || !himuonFlexCart || !himuonFlexCart.nonce) {
            return
        }
        const variationContent = $('.himuon-cart--variation-selection')

        if (variationContent.data('rendering')) return

        variationContent.data('rendering', true)
        setCartItemVariationLoading(true)
        $.ajax({
            url: wc_cart_fragments_params.wc_ajax_url.toString().replace('%%endpoint%%', 'himuon_render_variation'),
            type: 'POST',
            data: {
                productId: productId,
                nonce: himuonFlexCart.nonce,
                quantity: qty
            },
            complete: function () {
                variationContent.data('rendering', false)
                setCartItemVariationLoading(false)
            },
            success: function (data) {
                if (data && data.success && data.data && data.data.html) {
                    var $content = $('.himuon-cart--variation-content')
                    $content.html(data.data.html)
                    var $form = $content.find('form.variations_form')
                    if ($form.length) {
                        $form.wc_variation_form()
                        const attributesObj = JSON.parse(attributes)
                        for (const [key, value] of Object.entries(attributesObj)) {
                            $form.find(`#${key}`).val(value).trigger('change')
                        }
                    }
                }
            }
        })
    }

    const debounceRenderCartItemVariation = debounce((variationEl, qty) => {
        const { productId, cartItemKey, attributes } = variationEl.dataset
        const variationContent = document.querySelector('.himuon-cart--variation-selection')
        variationContent.classList.add('himuon-cart--show-variation')

        if (productId && cartItemKey && attributes) {
            variationContent.setAttribute('data-cart-item-key', cartItemKey)
            renderVariationForm(productId, attributes, qty)
        }
    }, 400)

    /**
     * =============================================================================
     * Variation Form: Update Cart Item
     * =============================================================================
     */

    const updateCartItemVariation = (updateBtn, form, cartItemKey) => {
        if (typeof wc_cart_fragments_params === 'undefined' || !himuonFlexCart || !himuonFlexCart.nonce) {
            return
        }
        const $form = $(form)

        const $formData = $form.serializeArray()
        $formData.push({ name: 'cart_item_key', value: cartItemKey })
        $formData.push({ name: 'nonce', value: himuonFlexCart.nonce })

        $btn = $(updateBtn)

        if ($btn.data('updatingVariationCartItem')) return

        $btn.data('updatingVariationCartItem', true)
        setCartItemVariationLoading(true)
        $.ajax({
            type: 'post',
            url: wc_cart_fragments_params.wc_ajax_url.toString().replace('%%endpoint%%', 'himuon_update_cart_item_variation'),
            data: $formData,
            success: (data) => {
                var fragments = data && data.fragments ? data.fragments : (data && data.data ? data.data.fragments : null)
                if (fragments) {
                    $.each(fragments, function (key, value) {
                        $(key).replaceWith(value)
                    })
                    $(document.body).trigger('wc_fragments_refreshed')
                }
            },
            complete: () => {
                setCartItemVariationLoading(false)
                $btn.data('updatingVariationCartItem', false)
            }
        })
    }


    /**
     * =============================================================================
     * Event Bindings
     * =============================================================================
     */

    $(document.body).on('added_to_cart removed_from_cart updated_cart_totals updated_wc_div', refreshSideCart)
    $(document.body).on('wc_fragments_refreshed', function () {
        setSideCartLoading(false)
    })

    setSideCartLoading(false)

    // Quantity Updates
    document.addEventListener('click', (e) => {
        const btn = e.target.closest('.himuon-cart--plus, .himuon-cart--minus')
        if (!btn) return

        const qtyWrap = btn.closest('.himuon-cart--quantity')
        const input = qtyWrap.querySelector('.himuon-cart--qty')


        if (!input || input.disabled) return

        const min = input.hasAttribute('min') ? parseInt(input.min, 10) : 1
        const max = input.hasAttribute('max') ? parseInt(input.max, 10) : Infinity
        const step = input.hasAttribute('step') ? parseInt(input.step, 10) : 1
        const current = parseInt(input.value, 10)
        const safeCurrent = Number.isNaN(current) ? min : current
        const isPlus = btn.classList.contains('himuon-cart--plus')

        if ((isPlus && safeCurrent >= max) || (!isPlus && safeCurrent <= min)) {
            updateQuantityButtons(qtyWrap, input)
            return
        }

        const next = isPlus ? safeCurrent + step : safeCurrent - step

        clampQuantity(input, next)
        updateQuantityButtons(qtyWrap, input)
        debounceCartItemQuantityUpdate(input)
    })

    document.querySelectorAll('.himuon-cart--quantity').forEach((wrap) => {
        const input = wrap.querySelector('.himuon-cart--qty')
        updateQuantityButtons(wrap, input)
    })

    // Render Variation Form
    document.addEventListener('click', (e) => {
        const target = e.target
        const variation = target.closest('.himuon-cart--variations')
        const variationContent = document.querySelector('.himuon-cart--variation-selection')

        if (variation) {
            const cartItemParent = variation.closest('.himuon-cart--item')
            const quantity = cartItemParent.querySelector('.himuon-cart--qty')

            debounceRenderCartItemVariation(variation, quantity.value)
            return
        }

        if (variationContent && variationContent.contains(target)) {
            return
        }

        if (variationContent && variationContent.classList.contains('himuon-cart--show-variation')) {
            variationContent.classList.remove('himuon-cart--show-variation')
        }
    })

    // Update Variation Form
    document.addEventListener('click', (e) => {
        const target = e.target
        const updateBtn = target.closest('.himuon-cart--variation-update-cart-item')
        if (!updateBtn || !(updateBtn instanceof HTMLButtonElement)) return


        const parent = updateBtn.closest('.himuon-cart--variation-selection')
        const form = parent.querySelector('.variations_form.cart')

        const { cartItemKey } = parent.dataset

        if (!cartItemKey || !form) return

        updateCartItemVariation(updateBtn, form, cartItemKey)
    })

})
