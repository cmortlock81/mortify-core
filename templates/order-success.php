<?php
if (!defined('ABSPATH')) exit;
if (empty($order_id)) return;

$order = wc_get_order($order_id);
if (!$order) return;

$status = wc_get_order_status_name($order->get_status());
?>

<div class="mortify-success">
  <h2>Order confirmed</h2>
  <p>Order #<?php echo esc_html($order->get_order_number()); ?></p>
  <span class="m-status"><?php echo esc_html($status); ?></span>

  <div class="m-items">
    <?php foreach ($order->get_items() as $item): ?>
      <div class="m-row">
        <span><?php echo esc_html($item->get_name()); ?> × <?php echo intval($item->get_quantity()); ?></span>
        <span><?php echo wp_kses_post(wc_price($item->get_total())); ?></span>
      </div>
    <?php endforeach; ?>
  </div>

  <p>You’ll receive a confirmation email shortly.</p>

  <div class="m-actions">
    <a href="/app/store" class="btn">Continue shopping</a>
    <a href="?mortify_reorder=<?php echo esc_attr($order_id); ?>" class="btn primary">Order again</a>
  </div>
</div>
