<?php
if ( ! class_exists( 'WooCommerce' ) ) {
    echo '<p>Store unavailable.</p>';
    return;
}

$products = wc_get_products( [
    'status' => 'publish',
    'limit'  => 12,
] );

if ( empty( $products ) ) {
    echo '<p>No products found.</p>';
    return;
}
?>
<div class="mortify-store">
<?php foreach ( $products as $product ) : ?>
  <div class="mortify-product">
    <a href="/app/product/<?php echo esc_attr( $product->get_slug() ); ?>">
      <?php echo $product->get_image(); ?>
      <h3><?php echo esc_html( $product->get_name() ); ?></h3>
      <span><?php echo wp_kses_post( $product->get_price_html() ); ?></span>
    </a>
  </div>
<?php endforeach; ?>
</div>
<style>
.mortify-store {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 12px;
}
.mortify-product {
  background: #fff;
  border: 1px solid #e5e7eb;
  border-radius: 8px;
  padding: 8px;
  text-align: center;
}
.mortify-product img {
  max-width: 100%;
  height: auto;
}
.mortify-product h3 {
  font-size: 14px;
  margin: 8px 0 4px;
}
</style>
