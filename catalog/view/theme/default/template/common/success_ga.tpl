<?php echo $header; ?>
<div class="container">
  <ul class="breadcrumb">
    <?php foreach ($breadcrumbs as $breadcrumb) { ?>
    <li><a href="<?php echo $breadcrumb['href']; ?>"><?php echo $breadcrumb['text']; ?></a></li>
    <?php } ?>
  </ul>
  <div class="row"><?php echo $column_left; ?>
    <?php if ($column_left && $column_right) { ?>
    <?php $class = 'col-sm-6'; ?>
    <?php } elseif ($column_left || $column_right) { ?>
    <?php $class = 'col-sm-9'; ?>
    <?php } else { ?>
    <?php $class = 'col-sm-12'; ?>
    <?php } ?>
    <div id="content" class="<?php echo $class; ?>"><?php echo $content_top; ?>
      <h1><?php echo $heading_title; ?></h1>
      <?php echo $text_message; ?>
      <div class="buttons">
        <div class="pull-right"><a href="<?php echo $continue; ?>" class="btn btn-primary"><?php echo $button_continue; ?></a></div>
      </div>
      <!-- <div>
        <?php echo "orderid: " . $gaOrderId; ?>
        <br /><?php echo "revenue: " . $gaRevenue; ?>
        <br /><?php echo "shipping: " . $gaShipping; ?>
        <br /><?php echo "tax: " . $gaTax; ?>
        <?php
        foreach ($gaCartContents as $item) {
          echo "name: " . $item['name'];
          echo "sku: " . $item['product_id']; //turns out sku is required: https://stackoverflow.com/a/19957296
          echo "price: " . $item['price'];
          echo "quantity: " . $item['quantity'];
        }
        ?> </div> -->
      <?php echo $content_bottom; ?></div>
    <?php echo $column_right; ?></div>
</div>

<script>
  ga('require', 'ecommerce');

  ga('ecommerce:addTransaction', {
    'id': '<?php echo $gaOrderId; ?>',
    'revenue': '<?php echo $gaRevenue; ?>',
    'shipping': '<?php echo $gaShipping; ?>',
    'tax': '<?php echo $gaTax; ?>'
  });

  <?php
  foreach ($gaCartContents as $item) { ?>
    ga('ecommerce:addItem', {
      'id': '<?php echo $gaOrderId; ?>',
      'name': '<?php echo $item['name']; ?>',
      'sku': '<?php echo $item['product_id']; ?>',
      'price': '<?php echo $item['price']; ?>',
      'quantity': '<?php echo $item['quantity']; ?>'
    });
  <?php } ?>

  ga('ecommerce:send');
</script>

<?php echo $footer; ?>
