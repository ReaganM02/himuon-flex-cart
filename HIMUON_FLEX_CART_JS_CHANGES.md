# himuon-flex-cart.js Changes

## Summary
This update improves side-cart loading behavior and exposes a loading hook for external modules.

## Features Added
- Added `isOpeningFromAddToCart` state to prevent duplicate fragment refreshes during add-to-cart open flow.
- Updated `openSideCart()` refresh guard so it skips refresh while cart is opening from `added_to_cart`.
- Improved `added_to_cart` flow to:
  - set opening state before waiting for fragment refresh,
  - open cart after `wc_fragments_refreshed`,
  - mark initial refresh as completed,
  - reset opening state.
- Added custom event listener:
  - `document.addEventListener('himuon:cart-loading', ...)`
  - allows external scripts (for example coupon AJAX) to toggle side-cart loading UI via `setSideCartLoading`.

## Impact
- Reduces redundant refresh work when products are added to cart.
- Keeps loading UX consistent across feature modules using event-based communication.
