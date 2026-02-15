jQuery(function ($) {
    /**
     * =============================================================================
     * Coupon Panel State + Helpers
     * =============================================================================
     */
    const getCouponWrapper = () => document.querySelector('.himuon-cart--coupon-item-wrapper')

    const openCouponPanel = () => {
        const wrapper = getCouponWrapper()
        if (!wrapper) {
            return
        }

        wrapper.classList.add('himuon-cart--couponing')
    }

    const closeCouponPanel = () => {
        const wrapper = getCouponWrapper()
        if (!wrapper) {
            return
        }

        wrapper.classList.remove('himuon-cart--couponing')
    }

    const getTargetElement = (event) => {
        if (!event || !event.target) {
            return null
        }

        if (event.target instanceof Element) {
            return event.target
        }

        if (event.target.nodeType === Node.TEXT_NODE && event.target.parentElement) {
            return event.target.parentElement
        }

        return null
    }

    const updateCouponSubmitState = (input) => {
        if (!input) {
            return
        }

        const form = input.closest('.himuon-cart--coupon-panel-form')
        if (!form) {
            return
        }

        const submit = form.querySelector('.himuon-cart--coupon-submit')
        if (!submit) {
            return
        }

        submit.disabled = input.value.trim() === ''
    }

    const updateCouponNotices = (element, noticesHtml) => {
        if (!element) {
            return
        }

        const wrapper = element.closest
            ? element.closest('.himuon-cart--coupon-form-wrapper') || element
            : element

        const noticesWrapper = wrapper.querySelector('.himuon-cart--coupon-notices')
        if (!noticesWrapper) {
            return
        }

        noticesWrapper.innerHTML = noticesHtml || ''
    }

    const replaceFragments = (fragments) => {
        if (!fragments || typeof fragments !== 'object') {
            return
        }

        Object.entries(fragments).forEach(([selector, html]) => {
            jQuery(selector).replaceWith(html)
        })
        jQuery(document.body).trigger('wc_fragments_refreshed')
    }

    const noticesHasError = (noticesHtml) => {
        if (!noticesHtml) {
            return false
        }
        return String(noticesHtml).indexOf('woocommerce-error') !== -1
    }

    const restoreCouponPanelAfterRefresh = (noticesHtml) => {
        const wrapper = getCouponWrapper()
        if (!wrapper) {
            return
        }

        // Keep panel open through fragment refresh without replaying the entrance animation.
        wrapper.classList.add('himuon-cart--coupon-no-anim')
        wrapper.classList.add('himuon-cart--couponing')

        const form = wrapper.querySelector('.himuon-cart--coupon-panel-form')
        if (!form) {
            wrapper.classList.remove('himuon-cart--coupon-no-anim')
            return
        }

        updateCouponNotices(wrapper, noticesHtml || '')

        if (noticesHasError(noticesHtml)) {
            return
        }

        const input = form.querySelector('.himuon-cart--coupon-input')
        if (!input) {
            wrapper.classList.remove('himuon-cart--coupon-no-anim')
            return
        }

        input.value = ''
        updateCouponSubmitState(input)

        requestAnimationFrame(() => {
            wrapper.classList.remove('himuon-cart--coupon-no-anim')
        })
    }

    const fallbackCouponErrorNotice = () => {
        const message =
            typeof himuonFlexCartCoupon !== 'undefined' &&
                himuonFlexCartCoupon &&
                himuonFlexCartCoupon.messages &&
                himuonFlexCartCoupon.messages.requestFailed
                ? String(himuonFlexCartCoupon.messages.requestFailed)
                : 'Unable to apply coupon right now. Please try again.'

        const escaped = jQuery('<div/>').text(message).html()
        return `<ul class="woocommerce-error" role="alert"><li>${escaped}</li></ul>`
    }

    const emitCartLoading = (loading) => {
        document.dispatchEvent(
            new CustomEvent('himuon:cart-loading', {
                detail: { loading: !!loading }
            })
        )
    }


    /**
     * =============================================================================
     * Event Bindings: Open/Close Coupon Panel
     * =============================================================================
     */
    document.addEventListener('click', (e) => {
        const target = getTargetElement(e)
        if (!target) {
            return
        }

        const couponTrigger = target.closest('.himuon-cart-coupon-form-wrapper')
        if (!couponTrigger) {
            return
        }

        e.preventDefault()
        openCouponPanel()
    })

    document.addEventListener('click', (e) => {
        const target = getTargetElement(e)
        if (!target) {
            return
        }

        const wrapper = getCouponWrapper()
        if (!wrapper || !wrapper.classList.contains('himuon-cart--couponing')) {
            return
        }

        if (target.closest('.himuon-cart-coupon-form-wrapper')) {
            return
        }

        const closeButton = target.closest('.himuon-cart--close-coupon-panel')
        if (closeButton) {
            closeCouponPanel()
            return
        }

        const couponContent = wrapper.querySelector('.himuon-cart--coupon-content')
        if (!couponContent) {
            return
        }

        if (!couponContent.contains(target)) {
            closeCouponPanel()
        }
    })

    /**
     * =============================================================================
     * Event Bindings: Coupon Form
     * =============================================================================
     */
    document.addEventListener('input', (e) => {
        const target = getTargetElement(e)
        if (!target) {
            return
        }

        const input = target.closest('.himuon-cart--coupon-input')
        if (!input) {
            return
        }

        updateCouponSubmitState(input)
    })

    document.addEventListener('submit', (e) => {
        const target = getTargetElement(e)
        if (!target) {
            return
        }

        const couponForm = target.closest('.himuon-cart--coupon-panel-form')
        if (!couponForm) {
            return
        }

        e.preventDefault()


        if (typeof wc_cart_fragments_params === 'undefined' || !himuonFlexCartCoupon || !himuonFlexCartCoupon.nonce) {
            return
        }

        const $form = $(couponForm)

        const data = $form.serializeArray()

        data.push({ name: 'nonce', value: himuonFlexCartCoupon.nonce })

        if ($form.data('adding')) return

        $form.data('adding', true)
        emitCartLoading(true)
        $.ajax({
            type: 'post',
            url: wc_cart_fragments_params.wc_ajax_url.toString().replace('%%endpoint%%', 'himuon_cart_add_coupon'),
            data: data,
            dataType: 'json',
            complete: () => {
                $form.data('adding', false)
                emitCartLoading(false)
            },
            success: (response) => {
                const payload = response && response.data ? response.data : {}
                replaceFragments(payload.fragments || null)

                // Side-cart fragments replace coupon DOM; restore open state + notices on the new nodes.
                restoreCouponPanelAfterRefresh(payload.notices_html || '')
            },
            error: () => {
                updateCouponNotices(couponForm, fallbackCouponErrorNotice())
            }
        })
    })
})
