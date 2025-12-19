<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! class_exists( 'WooCommerce' ) || ! function_exists( 'wc_get_product' ) ) {
    echo '<p>Product unavailable.</p>';
    return;
}

$slug = $_GET['mortify_product'] ?? '';
if ( ! $slug ) {
    echo '<p>Invalid product.</p>';
    return;
}

$post = get_page_by_path( $slug, OBJECT, 'product' );
if ( ! $post ) {
    echo '<p>Product not found.</p>';
    return;
}

$product = wc_get_product( $post->ID );
if ( ! $product ) {
    echo '<p>Product not found.</p>';
    return;
}

$is_variable = $product->is_type( 'variable' );
$currency    = function_exists('get_woocommerce_currency') ? get_woocommerce_currency() : 'GBP';
$locale      = function_exists('get_locale') ? get_locale() : 'en-GB';

$from_price = '';
$variations_json = '[]';
$variation_attributes = [];

if ( $is_variable ) {
    $variation_attributes = $product->get_variation_attributes(); // keys like 'pa_size'
    $available = $product->get_available_variations();

    // Determine min price for "From"
    $min = null;
    foreach ( $available as $v ) {
        if ( empty($v['display_price']) ) continue;
        $p = floatval($v['display_price']);
        if ( $min === null || $p < $min ) $min = $p;
    }
    if ( $min !== null ) {
        $from_price = wc_price( $min );
    } else {
        $from_price = $product->get_price_html();
    }

    // Reduce variation payload for JS matching
    $compact = [];
    foreach ( $available as $v ) {
        $compact[] = [
            'variation_id' => intval($v['variation_id'] ?? 0),
            'is_in_stock'  => ! empty($v['is_in_stock']),
            'attributes'   => (array)($v['attributes'] ?? []),
            'display_price'=> floatval($v['display_price'] ?? 0),
            'image'        => isset($v['image']['src']) ? esc_url_raw($v['image']['src']) : '',
        ];
    }
    $variations_json = wp_json_encode( $compact );
}
?>

<div class="mortify-product">
  <div class="mortify-product-title"><?php echo esc_html( $product->get_name() ); ?></div>

  <div class="mortify-product-image" id="mortify-image">
    <?php echo $product->get_image(); ?>
  </div>

  <div class="mortify-product-price" id="mortify-price">
    <?php
      if ( $is_variable ) {
        echo '<span class="mortify-price-prefix">From</span> <span class="mortify-price-value">' . wp_kses_post( $from_price ) . '</span>';
      } else {
        echo wp_kses_post( $product->get_price_html() );
      }
    ?>
  </div>

  <?php if ( $is_variable ) : ?>
    <div class="mortify-product-options" id="mortify-options" data-variations='<?php echo esc_attr( $variations_json ); ?>' data-locale="<?php echo esc_attr($locale); ?>" data-currency="<?php echo esc_attr($currency); ?>">
      <?php foreach ( $variation_attributes as $name => $options ) :
        $attr_key = 'attribute_' . sanitize_title( $name ); // e.g. attribute_pa_size
      ?>
        <div class="mortify-attr" data-attr="<?php echo esc_attr( $attr_key ); ?>">
          <div class="mortify-attr-label"><?php echo esc_html( wc_attribute_label( $name ) ); ?></div>
          <div class="mortify-chips" role="list">
            <?php foreach ( $options as $option ) : ?>
              <button type="button" class="mortify-chip" data-value="<?php echo esc_attr( $option ); ?>"><?php echo esc_html( $option ); ?></button>
            <?php endforeach; ?>
          </div>
        </div>
      <?php endforeach; ?>
      <div class="mortify-help" id="mortify-help" style="display:none;">Please choose options</div>
      <div class="mortify-summary" id="mortify-summary"></div>
    </div>
  <?php endif; ?>

  <div class="mortify-product-desc">
    <?php echo wp_kses_post( wpautop( $product->get_description() ) ); ?>
  </div>

  <form method="post" class="mortify-atc" id="mortify-atc-form">
    <input type="hidden" name="add-to-cart" value="<?php echo esc_attr( $product->get_id() ); ?>">
    <?php if ( $is_variable ) : ?>
      <input type="hidden" name="variation_id" id="mortify-variation-id" value="">
      <?php foreach ( $variation_attributes as $name => $options ) :
        $attr_key = 'attribute_' . sanitize_title( $name );
      ?>
        <input type="hidden" name="<?php echo esc_attr($attr_key); ?>" id="mortify-<?php echo esc_attr($attr_key); ?>" value="" required>
      <?php endforeach; ?>
    <?php endif; ?>

    <button type="submit" class="mortify-primary" id="mortify-add-to-cart" <?php echo $is_variable ? 'disabled' : ''; ?>>
      Add to cart
    </button>
  </form>
</div>

<style>
.mortify-product{max-width:680px;margin:0 auto}
.mortify-product-title{font-size:34px;line-height:1.1;font-weight:800;margin:8px 0 14px}
.mortify-product-image img{width:100%;height:auto;border-radius:16px;background:#f3f4f6}
.mortify-product-price{margin:14px 0 12px;font-size:18px;font-weight:700}
.mortify-price-prefix{opacity:.7;font-weight:600;margin-right:6px}
.mortify-product-options{margin:14px 0 10px}
.mortify-attr{margin-bottom:14px}
.mortify-attr-label{font-weight:700;margin-bottom:8px}
.mortify-chips{display:flex;flex-wrap:wrap;gap:10px}
.mortify-chip{padding:10px 14px;border-radius:999px;border:1px solid #d1d5db;background:#fff;font-weight:600}
.mortify-chip.active{background:#111827;color:#fff;border-color:#111827}
.mortify-chip:disabled{opacity:.45}
.mortify-help{margin-top:6px;color:#b91c1c;font-weight:600}
.mortify-summary{margin-top:10px;padding:10px 12px;border-radius:12px;background:#eef2ff;color:#111827;font-weight:700;display:none}
.mortify-chip[disabled]{opacity:.35}
.mortify-product-desc{margin:16px 0 18px;opacity:.9;line-height:1.5}
.mortify-atc{position:sticky;bottom:0;background:linear-gradient(to top, rgba(249,250,251,1), rgba(249,250,251,.85));padding:12px 0 6px}
.mortify-primary{width:100%;padding:14px 16px;border:0;border-radius:14px;background:#111827;color:#fff;font-size:16px;font-weight:800}
.mortify-primary:disabled{opacity:.45}
</style>

<?php if ( $is_variable ) : ?>
<script>
(function(){
  const optsEl = document.getElementById('mortify-options');
  const form = document.getElementById('mortify-atc-form');
  const btn = document.getElementById('mortify-add-to-cart');
  const priceEl = document.getElementById('mortify-price');
  const helpEl = document.getElementById('mortify-help');
  const summaryEl = document.getElementById('mortify-summary');
  const varIdEl = document.getElementById('mortify-variation-id');
  const imgWrap = document.getElementById('mortify-image');
  const imgEl = imgWrap ? imgWrap.querySelector('img') : null;

  if(!optsEl || !form || !btn) return;

  const variations = JSON.parse(optsEl.dataset.variations || '[]');
  const locale = optsEl.dataset.locale || 'en-GB';
  const currency = optsEl.dataset.currency || 'GBP';
  const fmt = (n) => {
    try { return new Intl.NumberFormat(locale, {style:'currency', currency}).format(n); }
    catch(e){ return '£' + Number(n).toFixed(2); }
  };

  // selected[attrKey] = value
  const selected = {};

  function setHidden(attrKey, value){
    const id = 'mortify-' + attrKey;
    const input = document.getElementById(id);
    if(input) input.value = value || '';
  }

  function requiredKeys(){
    const req = form.querySelectorAll('input[required]');
    return Array.from(req).map(i => i.name);
  }

  function allSelected(){
    const keys = requiredKeys();
    return keys.every(k => selected[k]);
  }

  function variationMatches(v, overrides){
    if(!v.is_in_stock) return false;
    const attrs = v.attributes || {};
    const keys = requiredKeys();
    for(const k of keys){
      const want = (overrides && k in overrides) ? overrides[k] : selected[k];
      if(!want) return false;
      if((attrs[k] || '') !== want) return false;
    }
    return true;
  }

  function findMatch(){
    return variations.find(v => variationMatches(v));
  }

  function existsWith(partial){
    // partial: some selected values; other required may be unselected
    return variations.some(v => {
      if(!v.is_in_stock) return false;
      const attrs = v.attributes || {};
      for(const k in partial){
        if(partial[k] && (attrs[k] || '') !== partial[k]) return false;
      }
      return true;
    });
  }

  function updateSummary(){
    if(!summaryEl) return;
    const keys = requiredKeys();
    const pairs = keys
      .filter(k => selected[k])
      .map(k => {
        const labelEl = optsEl.querySelector('.mortify-attr[data-attr="'+k+'"] .mortify-attr-label');
        const label = labelEl ? labelEl.textContent.trim() : k.replace('attribute_', '');
        return label + ': ' + selected[k];
      });
    if(pairs.length){
      summaryEl.textContent = pairs.join(' • ');
      summaryEl.style.display = 'block';
    } else {
      summaryEl.style.display = 'none';
    }
  }

  function updateOptionAvailability(){
    // Disable impossible options dynamically based on current selections
    optsEl.querySelectorAll('.mortify-attr').forEach(attrEl => {
      const attrKey = attrEl.dataset.attr;
      attrEl.querySelectorAll('.mortify-chip').forEach(chip => {
        const val = chip.dataset.value || '';
        const hypothetical = {...selected, [attrKey]: val};
        // Only test against currently selected other attrs (partial match)
        // i.e. does any in-stock variation satisfy all chosen attrs if we pick this?
        const partial = {};
        requiredKeys().forEach(k => {
          if(hypothetical[k]) partial[k] = hypothetical[k];
        });
        chip.disabled = !existsWith(partial);
      });
    });
  }

  function updateUI(){
    updateSummary();
    updateOptionAvailability();

    if(!allSelected()){
      btn.disabled = true;
      varIdEl.value = '';
      helpEl.style.display = 'none';
      return;
    }

    const match = findMatch();
    if(match && match.variation_id){
      varIdEl.value = match.variation_id;
      btn.disabled = false;
      helpEl.style.display = 'none';

      // Update price
      if(priceEl){
        priceEl.innerHTML = '<span class="mortify-price-value">' + fmt(match.display_price) + '</span>';
      }

      // Swap image if available
      if(imgEl && match.image){
        imgEl.src = match.image;
      }
    } else {
      btn.disabled = true;
      varIdEl.value = '';
      helpEl.textContent = 'This combination is unavailable';
      helpEl.style.display = 'block';
    }
  }

  optsEl.querySelectorAll('.mortify-attr').forEach(attrEl => {
    const attrKey = attrEl.dataset.attr;
    attrEl.querySelectorAll('.mortify-chip').forEach(chip => {
      chip.addEventListener('click', () => {
        if(chip.disabled) return;

        // single-select per group
        attrEl.querySelectorAll('.mortify-chip').forEach(c => c.classList.remove('active'));
        chip.classList.add('active');

        selected[attrKey] = chip.dataset.value || '';
        setHidden(attrKey, selected[attrKey]);

        updateUI();
      });
    });
  });

  // Guard against submitting without variation id
  form.addEventListener('submit', (e) => {
    if(btn.disabled || !varIdEl.value){
      e.preventDefault();
      helpEl.textContent = 'Please choose options';
      helpEl.style.display = 'block';
    }
  });

  updateUI();
})();
</script></script>
<?php endif; ?>


<script>
(function(){
  try {
    // Store referrer when entering product page
    if (document.referrer && document.referrer.indexOf('/app/') !== -1 && document.referrer.indexOf('/app/product') === -1) {
      sessionStorage.setItem('mortify_return_to', document.referrer);
    }

    const form = document.getElementById('mortify-atc-form');
    if(!form) return;

    form.addEventListener('submit', function(){
      const target = sessionStorage.getItem('mortify_return_to') || '/app/store';
      setTimeout(function(){
        window.location.href = target;
      }, 150);
    });
  } catch(e) {}
})();
</script>
