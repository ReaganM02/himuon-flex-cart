jQuery(function ($) {
    var isUpdatingCartItem = false

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
        isUpdatingCartItem = true
        setLoading(true)
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


    function setRenderVariationLoading(isLoading) {
        const $el = $('.himuon-cart--spinner-wrapper')
        if ($el.length) {
            $el.toggleClass('himuon-cart--show-variation-spinner', !!isLoading)
        }
    }

    function refreshSideCart() {
        if (typeof wc_cart_fragments_params === 'undefined') {
            return
        }
        if (isUpdatingCartItem) {
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

    const debouncedUpdate = debounce(async function (input) {
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

    function updateQuantityButtons(qtyWrap, input) {
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
        debouncedUpdate(input)
    })

    document.querySelectorAll('.himuon-cart--quantity').forEach((wrap) => {
        const input = wrap.querySelector('.himuon-cart--qty')
        updateQuantityButtons(wrap, input)
    })


    /**
     * Handle Variation Update
     */

    const renderVariationForm = (productId, attributes) => {
        console.log(attributes)
        if (typeof wc_cart_fragments_params === 'undefined' || !himuonFlexCart || !himuonFlexCart.nonce) {
            return
        }
        const variationContent = $('.himuon-cart--variation-selection')

        if (variationContent.data('rendering')) return

        variationContent.data('rendering', true)
        setRenderVariationLoading(true)
        $.ajax({
            url: wc_cart_fragments_params.wc_ajax_url.toString().replace('%%endpoint%%', 'himuon_render_variation'),
            type: 'POST',
            data: {
                productId: productId,
                nonce: himuonFlexCart.nonce
            },
            complete: function () {
                variationContent.data('rendering', false)
                setRenderVariationLoading(false)
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

    const updateVariationCartItem = ($form) => {
        console.log($form)
        $.ajax({
            type: 'post',
            url: wc_add_to_cart_params.wc_ajax_url.replace(
                '%%endpoint%%',
                'himuon_update_variation_cart_item'
            ),
        })
        // const variationId = $form.find('input[name="variation_id"]').val()
        // const data = $form.serializeArray()
        //     .filter(({ name }) => name !== 'add-to-cart')
        //     .map((field) => {
        //         console.log(field.name === 'variation_id')
        //         if (field.name === 'product_id') {
        //             return { name: 'product_id', value: variationId }
        //         }
        //         return field
        //     })

        // $(document.body).trigger('adding_to_cart', [$(btn), data])

        // $(document.body).trigger('adding_to_cart', [$(btn), data])

        // $.ajax({
        //     type: 'post',
        //     url: wc_add_to_cart_params.wc_ajax_url.replace(
        //         '%%endpoint%%',
        //         'add_to_cart'
        //     ),
        //     data: $.param(data),
        //     success: function (response) {
        //         if (!response) return

        //         // if (response.error && response.product_url) {
        //         //     window.location = response.product_url
        //         //     return
        //         // }
        //         console.log(response)
        //     }
        // })
    }

    const debounceVariationClick = debounce((variationEl) => {
        const { productId, cartItemKey, attributes } = variationEl.dataset
        const variationContent = document.querySelector('.himuon-cart--variation-selection')
        variationContent.classList.add('himuon-cart--show-variation')

        if (productId && cartItemKey && attributes) {
            variationContent.setAttribute('data-cart-item-key', cartItemKey)
            renderVariationForm(productId, attributes)
        }
    }, 400)



    document.addEventListener('click', (e) => {
        const target = e.target
        const variation = target.closest('.himuon-cart--variations')
        const variationContent = document.querySelector('.himuon-cart--variation-selection')

        if (variation) {
            debounceVariationClick(variation)
            return
        }

        if (variationContent && variationContent.contains(target)) {
            return
        }

        if (variationContent && variationContent.classList.contains('himuon-cart--show-variation')) {
            variationContent.classList.remove('himuon-cart--show-variation')
        }
    })

    // Form Submission
    $(document).on('submit', '.himuon-cart--variation-selection form.variations_form button[type="submit"]', function (e) {
        e.preventDefault()
        e.stopImmediatePropagation()
        // your custom AJAX
    })
    // document.addEventListener('submit', (e) => {
    //     const btn = e.target.closest('.himuon-cart--variation-selection form.variations_form button[type="submit"]')
    //     if (!btn) return
    //     e.preventDefault()
    //     e.stopPropagation()
    //     e.stopImmediatePropagation()

    //     const $form = $(btn.form)
    //     if ($form.length === 0) return

    //     updateVariationCartItem($form)
    // })
})
