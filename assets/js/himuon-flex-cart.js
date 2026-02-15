jQuery(function ($) {

    /**
     * =============================================================================
     * Side Cart State + Helpers
     * =============================================================================
     */
    var isUpdatingCartItem = false
    var himuonVariationImagePatched = false
    // Refresh fragments once on first open to pick up server-side changes without a page-load refresh.
    var hasRefreshedOnOpen = false
    var isOpeningFromAddToCart = false

    const preloadVariationImage = (variation, onReady) => {
        if (!variation || !variation.image || !variation.image.src) {
            onReady()
            return
        }

        let done = false
        const finalize = () => {
            if (done) return
            done = true
            onReady()
        }

        const image = new Image()
        image.onload = finalize
        image.onerror = finalize

        if (variation.image.srcset) {
            image.srcset = variation.image.srcset
        }
        if (variation.image.sizes) {
            image.sizes = variation.image.sizes
        }

        image.src = variation.image.src

        // Safety: don't block the swap forever if the image stalls.
        setTimeout(finalize, 1500)
    }

    const patchVariationImageUpdate = () => {
        if (himuonVariationImagePatched || !$.fn.wc_variations_image_update) {
            return
        }
        himuonVariationImagePatched = true
        const original = $.fn.wc_variations_image_update
        $.fn.wc_variations_image_update = function (variation) {
            if (this.closest('.himuon-flex-cart-plugin, .himuon-cart--edit-content, .himuon-cart--form-wrapper').length) {
                return this
            }
            const context = this
            preloadVariationImage(variation, () => {
                original.call(context, variation)
            })
            return this
        }
    }

    const getSideCart = () => { return $('#himuon-side-cart') }

    const setSideCartLoading = (isLoading) => {
        const $sideCart = getSideCart()
        if (!$sideCart.length) {
            return
        }
        $sideCart.toggleClass('is-loading', !!isLoading)
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

    const openCartItemActions = (handler) => {
        if (!handler) return
        const cartItemMainParent = handler.closest('.himuon-cart--cart-item')
        if (!cartItemMainParent) return

        const actions = cartItemMainParent.querySelector('.himuon-cart--actions')
        const cartItem = cartItemMainParent.querySelector('.himuon-cart--item')
        const container = cartItemMainParent.querySelector('.himuon-cart--actions-content')

        handler.classList.add('is-revealed')
        actions.style.width = `${container.clientWidth}px`
        cartItem.style.transform = `translateX(-${container.clientWidth}px)`
        handler.style.transform = `translateX(-${container.clientWidth}px)`
    }

    const closeCartItemActions = (handler) => {
        if (!handler) return
        const cartItemMainParent = handler.closest('.himuon-cart--cart-item')
        if (!cartItemMainParent) return

        const actions = cartItemMainParent.querySelector('.himuon-cart--actions')
        const cartItem = cartItemMainParent.querySelector('.himuon-cart--item')

        handler.classList.remove('is-revealed')
        actions.style.width = '0'
        cartItem.style.transform = 'none'
        handler.style.transform = 'none'
    }

    const closeAllCartItemActions = () => {
        document.querySelectorAll('.himuon-cart--cart-item-action.is-revealed').forEach(closeCartItemActions)
    }

    const openSideCart = () => {
        const sideCart = document.querySelector('.himuon-flex-cart-plugin')
        if (!sideCart) return

        if (!hasRefreshedOnOpen && !isOpeningFromAddToCart && typeof wc_cart_fragments_params !== 'undefined') {
            hasRefreshedOnOpen = true
            refreshSideCart()
        }

        // remove then add to guarantee transition
        sideCart.classList.remove('himuon-cart--show')
        requestAnimationFrame(() => {
            requestAnimationFrame(() => {
                sideCart.classList.add('himuon-cart--show')
            })
        })
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

        const $btn = $(updateBtn)

        if ($btn.data('updatingVariationCartItem')) return

        $btn.data('updatingVariationCartItem', true)
        setSideCartLoading(true)
        $.ajax({
            type: 'post',
            url: wc_cart_fragments_params.wc_ajax_url.toString().replace('%%endpoint%%', 'himuon_update_cart_item_variation'),
            data: $formData,
            success: (response) => {
                const payload = response && response.data ? response.data : response

                // Handle "same variation selected" case.
                if (payload && payload.no_change) {
                    const wrapper = document.querySelector('.himuon-cart--edit-item-wrapper')
                    if (wrapper) {
                        wrapper.classList.remove('himuon-cart--editing')
                    }
                    return
                }

                const fragments = payload && payload.fragments ? payload.fragments : null
                if (fragments) {
                    $.each(fragments, function (key, value) {
                        $(key).replaceWith(value)
                    })
                    $(document.body).trigger('wc_fragments_refreshed')
                }
            },
            complete: () => {
                setSideCartLoading(false)
                $btn.data('updatingVariationCartItem', false)
            }
        })
    }

    const updateCartItemSubscription = (updateBtn, form, cartItemKey) => {
        if (typeof wc_cart_fragments_params === 'undefined' || !himuonFlexCart || !himuonFlexCart.nonce) {
            return
        }
        if (!form) {
            return
        }

        const $form = $(form)
        const $formData = $form.serializeArray()
        $formData.push({ name: 'cart_item_key', value: cartItemKey })
        $formData.push({ name: 'nonce', value: himuonFlexCart.nonce })

        const $btn = $(updateBtn)
        if ($btn.data('updatingSubscriptionCartItem')) return

        $btn.data('updatingSubscriptionCartItem', true)
        setSideCartLoading(true)
        $.ajax({
            type: 'post',
            url: wc_cart_fragments_params.wc_ajax_url.toString().replace('%%endpoint%%', 'himuon_update_cart_item_subscription'),
            data: $formData,
            success: (response) => {
                const payload = response && response.data ? response.data : response

                if (payload && payload.no_change) {
                    const wrapper = document.querySelector('.himuon-cart--edit-item-wrapper')
                    if (wrapper) {
                        wrapper.classList.remove('himuon-cart--editing')
                    }
                    return
                }

                const fragments = payload && payload.fragments ? payload.fragments : null
                if (fragments) {
                    $.each(fragments, function (key, value) {
                        $(key).replaceWith(value)
                    })
                    $(document.body).trigger('wc_fragments_refreshed')
                }
            },
            complete: () => {
                setSideCartLoading(false)
                $btn.data('updatingSubscriptionCartItem', false)
            }
        })
    }

    /**
     * =============================================================================
     * Cart Item Actions: Delete
     * =============================================================================
     */

    const deleteCartItem = (el, cartItemKey) => {
        if (typeof wc_cart_fragments_params === 'undefined' || !himuonFlexCart || !himuonFlexCart.nonce) {
            return
        }

        const $el = $(el)

        if ($el.data('deleting')) return

        $el.data('deleting', true)
        isUpdatingCartItem = true
        setSideCartLoading(true)
        $.ajax({
            type: 'post',
            url: wc_cart_fragments_params.wc_ajax_url.toString().replace('%%endpoint%%', 'himuon_delete_cart_item'),
            data: {
                cart_item_key: cartItemKey,
                nonce: himuonFlexCart.nonce
            },
            complete: () => {
                setSideCartLoading(false)
                $el.data('deleting', false)
                isUpdatingCartItem = false
            },
            success: (data) => {
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

    /**
     * =============================================================================
     * Edit Cart Item
     * =============================================================================
     */
    const editCartItem = (el, cartItemKey) => {
        if (typeof wc_cart_fragments_params === 'undefined' || !himuonFlexCart || !himuonFlexCart.nonce) {
            return
        }
        const $editContent = $('.himuon-cart--edit-content')
        const $el = $(el)

        if ($el.data('editing')) return

        $el.data('editing', true)
        setSideCartLoading(true)
        $editContent.removeClass('is-loaded').addClass('is-loading').empty()
        let isEditLoaded = false
        $.ajax({
            type: 'post',
            url: wc_cart_fragments_params.wc_ajax_url.toString().replace('%%endpoint%%', 'himuon_cart_item_edit'),
            data: {
                cartItemKey: cartItemKey,
                nonce: himuonFlexCart.nonce
            },
            complete: () => {
                $el.data('editing', false)
                setSideCartLoading(false)
                $editContent.removeClass('is-loading')
                if (isEditLoaded) {
                    $editContent.addClass('is-loaded')
                }
            },
            success: (data) => {
                $editContent.html(data.data.form)
                isEditLoaded = true
                const $form = $editContent.find('form.variations_form')
                if ($form.length) {
                    bindEditVariationFormEvents($form, $editContent.get(0))
                    updateEditVariationPreview($editContent.get(0), null, true)
                    $form.wc_variation_form()

                    for (const [key, value] of Object.entries(data.data.attributes)) {
                        $form.find(`[name="${key}"]`).val(value)
                    }

                    $form.trigger('check_variations')
                    $form.find('select').trigger('change')
                }
            },
            error: () => {
                $el.removeClass('himuon-cart--editing')
            }
        })
    }

    /**
     * =============================================================================
     * Event Bindings
     * =============================================================================
     */

    $(document.body).on('added_to_cart removed_from_cart', refreshSideCart)
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

    // Update Variation Form
    document.addEventListener('click', (e) => {
        const target = e.target
        const updateBtn = target.closest('.himuon-cart--variation-update-cart-item')
        if (!updateBtn || !(updateBtn instanceof HTMLButtonElement)) return

        const { cartItemKey } = updateBtn.dataset
        if (!cartItemKey) return

        const form = document.querySelector('.himuon-cart--edit-content form')
        updateCartItemVariation(updateBtn, form, cartItemKey)
    })



    // Update subscription for single product
    document.addEventListener('click', (e) => {
        const target = e.target
        const updateBtn = target.closest('.himuon-cart--subscription-update-cart-item')
        if (!updateBtn || !(updateBtn instanceof HTMLButtonElement)) return

        const { cartItemKey } = updateBtn.dataset
        if (!cartItemKey) return

        const form = document.querySelector('.himuon-cart--edit-content .himuon-cart--subscription-edit-form')
        updateCartItemSubscription(updateBtn, form, cartItemKey)
    })

    // Cart Item Actions
    document.addEventListener('click', (e) => {
        const handler = e.target.closest('.himuon-cart--cart-item-action')
        if (!handler) {
            const openHandler = document.querySelector('.himuon-cart--cart-item-action.is-revealed')
            if (!openHandler) return

            const clickedCartItem = e.target.closest('.himuon-cart--cart-item')
            const openCartItem = openHandler.closest('.himuon-cart--cart-item')

            if (!clickedCartItem || (openCartItem && clickedCartItem !== openCartItem)) {
                closeAllCartItemActions()
            }
            return
        }

        e.preventDefault()

        const isOpen = handler.classList.contains('is-revealed')
        closeAllCartItemActions()

        if (!isOpen) {
            openCartItemActions(handler)
        }
    })

    document.addEventListener('click', (e) => {
        const deleteEl = e.target.closest('.himuon-cart--action-delete')
        if (!deleteEl) return

        const { cartItemKey } = deleteEl.dataset

        if (!cartItemKey) return

        deleteCartItem(deleteEl, cartItemKey)
    })

    document.addEventListener('click', (e) => {
        const handler = e.target.closest('.himuon-side-cart-handler')
        const sideCartWrapper = document.querySelector('.himuon-flex-cart-plugin')

        const sideCart = document.getElementById('himuon-side-cart')

        if (!sideCart) return

        if (handler) {
            if (sideCartWrapper.classList.contains('himuon-cart--show')) {
                sideCartWrapper.classList.remove('himuon-cart--show')
            } else {
                openSideCart()
            }
            return
        }

        const clickedInsideCart = sideCart.contains(e.target)
        if (!clickedInsideCart) {
            sideCartWrapper.classList.remove('himuon-cart--show')
        }
    })

    // Close side cart
    document.addEventListener('click', (e) => {
        const closeCartEl = e.target.closest('.himuon-cart--close')
        if (!closeCartEl) return
        const sideCartWrapper = document.querySelector('.himuon-flex-cart-plugin')
        sideCartWrapper.classList.remove('himuon-cart--show')
    })

    // Trigger when added to cart
    $(document.body).on('added_to_cart', function () {
        isOpeningFromAddToCart = true
        $(document.body).one('wc_fragments_refreshed', function () {
            openSideCart()
            hasRefreshedOnOpen = true
            isOpeningFromAddToCart = false
        })
    })

    // Edit Cart Item 
    document.addEventListener('click', (e) => {
        const editHandler = e.target.closest('.himuon-cart--action-edit')
        if (!editHandler) return

        closeAllCartItemActions()

        const { cartItemKey } = editHandler.dataset

        const wrapper = document.querySelector('.himuon-cart--edit-item-wrapper')
        if (!wrapper) return

        wrapper.classList.add('himuon-cart--editing')
        editCartItem(wrapper, cartItemKey)
    })

    // Close Edit Cart Item
    document.addEventListener('click', (e) => {
        const wrapper = document.querySelector('.himuon-cart--edit-item-wrapper')
        if (!wrapper || !wrapper.classList.contains('himuon-cart--editing')) return

        // Don’t close when clicking the edit button itself
        if (e.target.closest('.himuon-cart--action-edit')) return

        const editContent = wrapper.querySelector('.himuon-cart--edit-content')
        if (!editContent) return

        if (!editContent.contains(e.target)) {
            wrapper.classList.remove('himuon-cart--editing')
        }
    })

    // Close Edit Cart Item via close icon/button
    document.addEventListener('click', (e) => {
        const closeEditPanel = e.target.closest('.himuon-cart--close-edit-panel')
        if (!closeEditPanel) return

        const wrapper = document.querySelector('.himuon-cart--edit-item-wrapper')
        if (!wrapper) return

        wrapper.classList.remove('himuon-cart--editing')
    })


    // Track Variation Value
    document.addEventListener('change', (e) => {
        const editContent = e.target.closest('.himuon-cart--edit-content')
        if (!editContent) return
        const selects = Array.from(editContent.querySelectorAll('select'))
        const anyEmpty = selects.some((select) => select.value.trim() === '')

        const updateBtn = editContent.querySelector('.himuon-cart--variation-update-cart-item')
        if (updateBtn) {
            updateBtn.disabled = anyEmpty
        }
    })

    // Apply variation image patch early if available.
    patchVariationImageUpdate()

    const getEditContentVariation = (editContent) => {
        if (!editContent) return null
        const form = editContent.querySelector('form.variations_form')
        if (!form) return null
        return $(form).data('himuonVariation') || null
    }

    const updateEditVariationPreview = (editContent, variation, useInitial) => {
        if (!editContent) return
        const preview = editContent.querySelector('.himuon-cart--variation-preview')
        if (!preview) return

        const imageData = variation && variation.image ? variation.image : null
        const nextSrc = useInitial ? preview.dataset.initialSrc : (imageData && imageData.src ? imageData.src : '')
        const nextSrcset = useInitial ? preview.dataset.initialSrcset : (imageData && imageData.srcset ? imageData.srcset : '')
        const nextSizes = useInitial ? preview.dataset.initialSizes : (imageData && imageData.sizes ? imageData.sizes : '')
        const nextAlt = useInitial ? preview.dataset.initialAlt : (imageData && imageData.alt ? imageData.alt : (preview.dataset.initialAlt || ''))

        if (!nextSrc) {
            return
        }

        let img = preview.querySelector('img.himuon-cart--variation-preview-image')
        if (!img) {
            img = document.createElement('img')
            img.className = 'himuon-cart--variation-preview-image'
            preview.appendChild(img)
        }

        img.src = nextSrc
        if (nextSrcset) {
            img.srcset = nextSrcset
        } else {
            img.removeAttribute('srcset')
        }

        if (nextSizes) {
            img.sizes = nextSizes
        } else {
            img.removeAttribute('sizes')
        }

        img.alt = nextAlt
    }

    const bindEditVariationFormEvents = ($form, editContent) => {
        if (!$form || !$form.length || !editContent) {
            return
        }

        $form.off('.himuonSideCart')

        // Keep side-cart variation events from leaking to document-level listeners.
        $form.on('found_variation.himuonSideCart', function (e, variation) {
            console.log(variation)
            e.stopPropagation()
            $(this).data('himuonVariation', variation || null)
            updateEditVariationPreview(editContent, variation || null, false)
            updateVariationButtonState(editContent)
        })

        $form.on('reset_data.himuonSideCart', function (e) {
            e.stopPropagation()
            $(this).data('himuonVariation', null)
            updateEditVariationPreview(editContent, null, true)
            updateVariationButtonState(editContent)
        })

        $form.on('show_variation.himuonSideCart hide_variation.himuonSideCart woocommerce_variation_has_changed.himuonSideCart', function (e) {
            e.stopPropagation()
        })

        $form.on('change.himuonSideCart', 'select', function (e) {
            e.stopPropagation()
            updateVariationButtonState(editContent)
        })
    }

    const updateVariationButtonState = (editContent) => {
        const updateBtn = editContent.querySelector('.himuon-cart--variation-update-cart-item')
        if (!updateBtn) return

        const selects = Array.from(editContent.querySelectorAll('select'))
        const anyEmpty = selects.some((select) => select.value.trim() === '')

        const v = getEditContentVariation(editContent)
        const inStock = v && (v.is_in_stock === true || v.is_in_stock === '1')
        const purchasable = v && (v.is_purchasable === true || v.is_purchasable === '1')

        const shouldDisable = anyEmpty || !(inStock && purchasable)
        updateBtn.disabled = shouldDisable
        updateBtn.toggleAttribute('disabled', shouldDisable)
    }

    document.addEventListener('change', (e) => {
        const editContent = e.target.closest('.himuon-cart--edit-content')
        if (!editContent) return
        updateVariationButtonState(editContent)
    })

    // Listen for external loading requests (e.g. coupon JS)
    document.addEventListener('himuon:cart-loading', (e) => {
        const loading = !!(e && e.detail && e.detail.loading)
        setSideCartLoading(loading)
    })
})
