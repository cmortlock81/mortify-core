<?php
if ( ! defined( 'ABSPATH' ) ) exit;
if ( ! class_exists( 'WooCommerce' ) ) {
    echo '<p>Cart unavailable.</p>';
    return;
}

$cart = WC()->cart;
if ( ! $cart || $cart->is_empty() ) {
    echo '<p>Your cart is empty.</p>';
    return;
}

$ajax_url = admin_url('admin-ajax.php');
?>

<div class="mortify-cart" data-ajax="<?php echo esc_url($ajax_url); ?>">

<?php foreach ( $cart->get_cart() as $key => $item ) :
    $product = $item['data'];
    if ( ! $product ) continue;
?>
  <div class="mortify-cart-item" data-key="<?php echo esc_attr($key); ?>">
    <div class="mortify-cart-thumb">
      <?php echo $product->get_image( 'thumbnail' ); ?>
    </div>

    <div class="mortify-cart-body">
      <div class="mortify-cart-title"><?php echo esc_html( $product->get_name() ); ?></div>
      <div class="mortify-cart-price"><?php echo wc_price( $product->get_price() ); ?></div>

      <div class="mortify-cart-qty">
        <button type="button" class="mortify-qty" data-act="dec">−</button>
        <span class="mortify-qty-val"><?php echo intval( $item['quantity'] ); ?></span>
        <button type="button" class="mortify-qty" data-act="inc">+</button>
      </div>
    </div>

    <div class="mortify-cart-line">
      <div class="mortify-line-total"><?php echo wc_price( $item['line_total'] ); ?></div>
      <button type="button" class="mortify-remove" data-act="remove">Remove</button>
    </div>
  </div>
<?php endforeach; ?>

<div class="mortify-cart-footer">
  <div class="mortify-cart-subtotal">
    Subtotal: <span id="mortify-subtotal"><?php echo wc_price( $cart->get_subtotal() ); ?></span>
  </div>
  <a class="mortify-checkout" href="/app/checkout">Proceed to checkout</a>
</div>

</div>

<style>
.mortify-cart{max-width:720px;margin:0 auto}
.mortify-cart-item{display:flex;gap:12px;padding:12px;border-bottom:1px solid #e5e7eb}
.mortify-cart-thumb img{width:72px;height:72px;object-fit:cover;border-radius:12px}
.mortify-cart-body{flex:1}
.mortify-cart-title{font-weight:700}
.mortify-cart-price{opacity:.7;margin-bottom:6px}
.mortify-cart-qty{display:flex;align-items:center;gap:10px}
.mortify-qty{width:36px;height:36px;border-radius:10px;border:1px solid #d1d5db;background:#fff;font-size:18px;font-weight:800}
.mortify-qty-val{min-width:22px;text-align:center;font-weight:800}
.mortify-cart-line{text-align:right;min-width:96px}
.mortify-line-total{font-weight:800}
.mortify-remove{background:none;border:0;color:#b91c1c;font-weight:700;margin-top:6px}
.mortify-cart-footer{padding:16px 12px;position:sticky;bottom:0;background:#f9fafb}
.mortify-checkout{display:block;margin-top:10px;background:#111827;color:#fff;text-align:center;padding:14px;border-radius:14px;font-weight:800}
</style>

<script>
(function(){
  const root = document.querySelector('.mortify-cart');
  if(!root) return;
  const ajaxUrl = root.dataset.ajax;

  async function post(data){
    const body = new URLSearchParams(data);
    const res = await fetch(ajaxUrl, {
      method: 'POST',
      headers: {'Content-Type':'application/x-www-form-urlencoded'},
      body
    });
    return res.json();
  }

  function setItem(el, payload){
    el.querySelector('.mortify-qty-val').textContent = payload.qty;
    el.querySelector('.mortify-line-total').textContent = payload.line_total;
  }

  root.addEventListener('click', async (e) => {
    const btn = e.target.closest('button');
    if(!btn) return;

    const itemEl = btn.closest('.mortify-cart-item');
    if(!itemEl) return;
    const key = itemEl.dataset.key;
    const act = btn.dataset.act;

    btn.disabled = true;
    try {
      const json = await post({ action:'mortify_update_cart', key, act });
      if(!json || !json.success){ throw new Error('Update failed'); }

      if(json.data && json.data.removed){
        itemEl.remove();
      } else if(json.data && json.data.item){
        setItem(itemEl, json.data.item);
      }

      if(json.data && json.data.subtotal){
        const sub = document.getElementById('mortify-subtotal');
        if(sub) sub.textContent = json.data.subtotal;
      }

      // If cart emptied, reload to show empty state
      if(json.data && json.data.cart_empty){
        window.location.reload();
      }
    } catch(err){
      console.log(err);
      window.location.reload();
    }
    finally {
      btn.disabled = false;
    }
  });
})();
</script>
