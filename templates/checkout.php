<?php
if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! class_exists( 'WooCommerce' ) || ! function_exists( 'WC' ) ) {
    echo '<p>Checkout unavailable.</p>';
    return;
}

if ( ! WC()->cart || WC()->cart->is_empty() ) {
    echo '<p>Your cart is empty.</p>';
    return;
}

$checkout = WC()->checkout();

// Render notices inline (validation errors, etc.)
wc_print_notices();

// Helper: safe plain-text money for sticky footer.
function mortify2026_plain_money( $html ) {
    return html_entity_decode( wp_strip_all_tags( $html ), ENT_QUOTES, get_bloginfo('charset') );
}

$total_plain = mortify2026_plain_money( WC()->cart->get_total() );
?>

<div class="mortify-checkout">

  <div class="mortify-checkout-summary" id="mortify-summary" style="display:none;">
    <div class="m-card">
      <div class="m-card-title">Order summary</div>
      <div class="m-summary-items">
        <?php foreach ( WC()->cart->get_cart() as $item ) :
          $p = $item['data'];
          if ( ! $p ) continue;
        ?>
          <div class="m-row">
            <div class="m-row-left">
              <div class="m-name"><?php echo esc_html( $p->get_name() ); ?></div>
              <div class="m-meta">Qty: <?php echo intval( $item['quantity'] ); ?></div>
            </div>
            <div class="m-row-right"><?php echo wp_kses_post( wc_price( $item['line_total'] ) ); ?></div>
          </div>
        <?php endforeach; ?>
      </div>
      <div class="m-divider"></div>
      <div class="m-row">
        <div class="m-row-left">Subtotal</div>
        <div class="m-row-right"><?php echo wp_kses_post( wc_price( WC()->cart->get_subtotal() ) ); ?></div>
      </div>
      <?php if ( WC()->cart->needs_shipping() ) : ?>
        <div class="m-row">
          <div class="m-row-left">Shipping</div>
          <div class="m-row-right">Calculated at next step</div>
        </div>
      <?php endif; ?>
      <div class="m-divider"></div>
      <div class="m-row m-total">
        <div class="m-row-left">Total</div>
        <div class="m-row-right"><?php echo esc_html( $total_plain ); ?></div>
      </div>
    </div>
  </div>

  <form name="checkout" method="post" class="checkout woocommerce-checkout" id="mortify-checkout-form"
        action="<?php echo esc_url( wc_get_checkout_url() ); ?>" enctype="multipart/form-data">

    <div class="m-section" data-sec="contact">
      <button type="button" class="m-sec-head" aria-expanded="true">
        <span>Contact</span><span class="m-sec-chev">▾</span>
      </button>
      <div class="m-sec-body">
        <?php
        $fields = $checkout->get_checkout_fields();
        // Email is required; phone optional by default (Woo config may override).
        if ( isset( $fields['billing']['billing_email'] ) ) {
          woocommerce_form_field( 'billing_email', $fields['billing']['billing_email'], $checkout->get_value('billing_email') );
        }
        if ( isset( $fields['billing']['billing_phone'] ) ) {
          woocommerce_form_field( 'billing_phone', $fields['billing']['billing_phone'], $checkout->get_value('billing_phone') );
        }
        ?>
      </div>
    </div>

    <div class="m-section" data-sec="billing">
      <button type="button" class="m-sec-head" aria-expanded="false">
        <span>Billing</span><span class="m-sec-chev">▾</span>
      </button>
      <div class="m-sec-body" style="display:none;">
        <?php
        // Render all billing fields except email/phone (already in Contact)
        if ( ! empty( $fields['billing'] ) ) {
          foreach ( $fields['billing'] as $key => $field ) {
            if ( in_array( $key, ['billing_email','billing_phone'], true ) ) continue;
            woocommerce_form_field( $key, $field, $checkout->get_value( $key ) );
          }
        }
        ?>
      </div>
    </div>

    <?php if ( WC()->cart->needs_shipping() ) : ?>
    <div class="m-section" data-sec="shipping">
      <button type="button" class="m-sec-head" aria-expanded="false">
        <span>Shipping</span><span class="m-sec-chev">▾</span>
      </button>
      <div class="m-sec-body" style="display:none;">
        <?php
        // Standard Woo "Ship to different address" checkbox
        do_action( 'woocommerce_checkout_shipping' );
        ?>
      </div>
    </div>
    <?php endif; ?>

    <div class="m-section" data-sec="payment">
      <button type="button" class="m-sec-head" aria-expanded="true">
        <span>Payment</span><span class="m-sec-chev">▾</span>
      </button>
      <div class="m-sec-body">
        <?php
        // Coupon block
        if ( wc_coupons_enabled() ) :
        ?>
          <div class="m-coupon">
            <button type="button" class="m-coupon-toggle" aria-expanded="false">Add coupon</button>
            <div class="m-coupon-body" style="display:none;">
              <?php woocommerce_checkout_coupon_form(); ?>
            </div>
          </div>
        <?php endif; ?>

        <div class="m-pay">
          <?php
          // Order review + payment methods
          do_action( 'woocommerce_checkout_order_review' );
          ?>
        </div>
      </div>
    </div>

    <?php
    // Terms + required hidden fields / nonce
    do_action( 'woocommerce_checkout_after_customer_details' );
    ?>

  </form>

  <div class="m-sticky">
    <div class="m-sticky-top">
      <div class="m-total-label">Total</div>
      <div class="m-total-val" id="mortify-sticky-total"><?php echo esc_html( $total_plain ); ?></div>
    </div>
    <div class="m-sticky-actions">
      <button type="button" class="m-summary-btn" id="mortify-summary-btn">View order summary</button>
      <button type="button" class="m-pay-btn" id="mortify-pay-btn">Pay now<span class="mortify-pay-subtext">Secure payment • No card details stored</span></button>
    </div>
  </div>

</div>

<style>
.mortify-checkout{max-width:720px;margin:0 auto;padding-bottom:110px}
.m-card{background:#fff;border:1px solid #e5e7eb;border-radius:16px;padding:14px}
.m-card-title{font-weight:800;margin-bottom:10px}
.m-row{display:flex;justify-content:space-between;gap:12px;padding:8px 0}
.m-name{font-weight:700}
.m-meta{opacity:.7;font-size:13px}
.m-divider{height:1px;background:#e5e7eb;margin:10px 0}
.m-total{font-weight:900}
.m-section{margin:12px 0;background:#fff;border:1px solid #e5e7eb;border-radius:16px;overflow:hidden}
.m-sec-head{width:100%;display:flex;justify-content:space-between;align-items:center;padding:14px 14px;background:#fff;border:0;font-weight:900;font-size:16px}
.m-sec-body{padding:14px}
.m-sec-chev{opacity:.7}
/* Woo fields */
.woocommerce form .form-row{margin:0 0 12px}
.woocommerce form .form-row input.input-text,
.woocommerce form .form-row select,
.woocommerce form .form-row textarea{width:100%;border:1px solid #d1d5db;border-radius:12px;padding:12px 12px;font-size:16px}
.woocommerce form .form-row label{font-weight:700;margin-bottom:6px;display:block}
/* Notices */
.woocommerce-error,.woocommerce-message,.woocommerce-info{border-radius:14px;padding:12px 14px;margin:10px 0;border:1px solid #e5e7eb}
/* Sticky footer */
.m-sticky{position:fixed;left:0;right:0;bottom:56px;background:rgba(249,250,251,.96);backdrop-filter:saturate(180%) blur(12px);border-top:1px solid #e5e7eb;padding:12px 14px}
.m-sticky-top{display:flex;justify-content:space-between;align-items:center;margin-bottom:10px}
.m-total-label{opacity:.7;font-weight:700}
.m-total-val{font-weight:900}
.m-sticky-actions{display:flex;gap:10px}
.m-summary-btn{flex:1;border:1px solid #d1d5db;background:#fff;border-radius:14px;padding:12px;font-weight:800}
.m-pay-btn{flex:1;border:0;background:#111827;color:#fff;border-radius:14px;padding:12px;font-weight:900}
/* Summary overlay */
.mortify-checkout-summary{position:fixed;left:0;right:0;top:52px;bottom:56px;background:rgba(17,24,39,.35);padding:14px;overflow:auto;z-index:999}
.mortify-checkout-summary .m-card{max-width:720px;margin:0 auto}
/* Coupon */
.m-coupon{margin-bottom:12px}
.m-coupon-toggle{border:1px solid #d1d5db;background:#fff;border-radius:12px;padding:10px 12px;font-weight:800}
.m-coupon-body{margin-top:10px}
/* Payment box polish */
#order_review, .woocommerce-checkout-review-order{background:#fff;border:1px solid #e5e7eb;border-radius:16px;padding:12px}
</style>

<script>
(function(){
  const form = document.getElementById('mortify-checkout-form');
  const payBtn = document.getElementById('mortify-pay-btn');
  const secHeads = document.querySelectorAll('.m-sec-head');
  const sumBtn = document.getElementById('mortify-summary-btn');
  const sum = document.getElementById('mortify-summary');

  // Collapse sections
  secHeads.forEach(h => {
    h.addEventListener('click', () => {
      const body = h.parentElement.querySelector('.m-sec-body');
      const open = h.getAttribute('aria-expanded') === 'true';
      h.setAttribute('aria-expanded', open ? 'false' : 'true');
      body.style.display = open ? 'none' : 'block';
    });
  });

  // Summary toggle
  if(sumBtn && sum){
    sumBtn.addEventListener('click', () => {
      const showing = sum.style.display !== 'none';
      sum.style.display = showing ? 'none' : 'block';
      sumBtn.textContent = showing ? 'View order summary' : 'Close summary';
    });
    sum.addEventListener('click', (e) => {
      if(e.target === sum){
        sum.style.display = 'none';
        sumBtn.textContent = 'View order summary';
      }
    });
  }

  // Pay now triggers Woo checkout submit
  if(payBtn && form){
    payBtn.addEventListener('click', () => {
      payBtn.disabled = true;
      payBtn.textContent = 'Processing…';
      form.submit();
    });
  }
})();
</script>


<script>
(function () {
  const payBtn = document.getElementById('mortify-pay-btn');
  const form = document.querySelector('form.checkout');
  if (!payBtn || !form) return;

  payBtn.setAttribute('aria-live', 'polite');

  payBtn.addEventListener('click', function (e) {
    e.preventDefault();
    if (payBtn.disabled) return;

    payBtn.disabled = true;
    payBtn.setAttribute('aria-disabled', 'true');
    payBtn.dataset.originalText = payBtn.textContent.trim();
    payBtn.textContent = 'Processing…';

    const ev = new Event('submit', { bubbles: true, cancelable: true });
    const ok = form.dispatchEvent(ev);

    if (!ok) restore();
  });

  document.body.addEventListener('checkout_error', restore);

  function restore() {
    payBtn.disabled = false;
    payBtn.removeAttribute('aria-disabled');
    payBtn.textContent = payBtn.dataset.originalText || 'Pay now';

    const err = document.querySelector('.woocommerce-error, .woocommerce-notice');
    if (err) {
      err.setAttribute('tabindex','-1');
      err.focus();
      err.scrollIntoView({behavior:'smooth', block:'center'});
    }
  }
})();
</script>
