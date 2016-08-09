<?php
$product_post_id = get_the_ID();
$title = get_the_title( $product_post_id );
$cat = get_the_terms ( $product_post_id, 'system_type' );

$meta = get_post_meta( $product_post_id );
$cmb = cmb2_get_metabox( 'sqms-product-overview-meta', $product_post_id );


echo '<h1>Product Selection: ' . $title .'</h1>';
echo '<h3>Product Selection Category: ' . esc_html( $cat[0]->name ) .'</h3>'; ?>

<table>
<?php
foreach( $meta as $field_id => $value ) {
    if( $value[0] !== 'NA' ) {
        $field = $cmb->get_field( $field_id );
        ?>
    <tr>
        <td><?php echo $field->args( 'name' ); ?></td>
        <td><?php echo $field->escaped_value(); ?></td>
    </tr>
    <?php } }?>
</table>

