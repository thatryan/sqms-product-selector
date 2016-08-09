
<!-- customer info table -->
<table width="99%" border="0" cellpadding="1" cellspacing="0" bgcolor="#EAEAEA">
	<tr>
		<td>
			<table width="100%" border="0" cellpadding="5" cellspacing="0" bgcolor="#FFFFFF">
				<tbody>
					<tr>
						<td colspan="2" style="font-size:14px;font-weight:bold;background-color:#eee;border-bottom:1px solid #dfdfdf;padding:7px 7px">Customer's Contact Information</td>
					</tr>
					<tr bgcolor="#EAF2FA">
						<td colspan="2">
							<font style="font-family:sans-serif;font-size:12px"><strong>Name</strong></font>
						</td>
						</tr>
					<tr bgcolor="#FFFFFF">
						<td width="20">&nbsp;</td>
						<td>
							<font style="font-family:sans-serif;font-size:12px"><?php echo rgar( $entry, '11.2' ); ?></font>
						</td>
					</tr>
					<tr bgcolor="#EAF2FA">
						<td colspan="2">
							<font style="font-family:sans-serif;font-size:12px"><strong>Address</strong></font>
						</td>
						</tr>
					<tr bgcolor="#FFFFFF">
						<td width="20">&nbsp;</td>
						<td>
							<font style="font-family:sans-serif;font-size:12px"><?php echo rgar( $entry, '47.1' ); ?></font>
						</td>
					</tr>
					<tr bgcolor="#EAF2FA">
						<td colspan="2">
							<font style="font-family:sans-serif;font-size:12px"><strong>Phone</strong></font>
						</td>
						</tr>
					<tr bgcolor="#FFFFFF">
						<td width="20">&nbsp;</td>
						<td>
							<font style="font-family:sans-serif;font-size:12px"><?php echo rgar( $entry, '48' ); ?></font>
						</td>
					</tr>
					<tr bgcolor="#EAF2FA">
						<td colspan="2">
							<font style="font-family:sans-serif;font-size:12px"><strong>Email</strong></font>
						</td>
						</tr>
					<tr bgcolor="#FFFFFF">
						<td width="20">&nbsp;</td>
						<td>
							<font style="font-family:sans-serif;font-size:12px"><?php echo rgar( $entry, '12' ); ?></font>
						</td>
					</tr>
					<tr bgcolor="#EAF2FA">
						<td colspan="2">
							<font style="font-family:sans-serif;font-size:12px"><strong>Best Day To Contact</strong></font>
						</td>
						</tr>
					<tr bgcolor="#FFFFFF">
						<td width="20">&nbsp;</td>
						<td>
							<font style="font-family:sans-serif;font-size:12px"><?php echo rgar( $entry, '49' ); ?></font>
						</td>
					</tr>
					<tr bgcolor="#EAF2FA">
						<td colspan="2">
							<font style="font-family:sans-serif;font-size:12px"><strong>Best Time For Contact</strong></font>
						</td>
						</tr>
					<tr bgcolor="#FFFFFF">
						<td width="20">&nbsp;</td>
						<td>
							<font style="font-family:sans-serif;font-size:12px"><?php echo rgar( $entry, '50' ); ?></font>
						</td>
					</tr>
				</tbody>
			</table>
		</td>
	</tr>
</table>
<!-- Customer interest table -->
<table width="99%" border="0" cellpadding="1" cellspacing="0" bgcolor="#EAEAEA">
	<tr>
		<td>
			<table width="100%" border="0" cellpadding="5" cellspacing="0" bgcolor="#FFFFFF">
				<tbody>
					<tr>
						<td colspan="2" style="font-size:14px;font-weight:bold;background-color:#eee;border-bottom:1px solid #dfdfdf;padding:7px 7px">Customer is Interested in:</td>
					</tr>
					<tr bgcolor="#EAF2FA">
						<td colspan="2">
							<font style="font-family:sans-serif;font-size:12px"><strong>Microff Financing</strong></font>
						</td>
					</tr>
					<tr bgcolor="#FFFFFF">
						<td width="20">&nbsp;</td>
						<td>
							<font style="font-family:sans-serif;font-size:12px"><?php echo rgar( $entry, '9' ); ?></font>
						</td>
					</tr>
					<tr bgcolor="#EAF2FA">
						<td colspan="2">
							<font style="font-family:sans-serif;font-size:12px"><strong>Extended Warranty</strong></font>
						</td>
					</tr>
					<tr bgcolor="#FFFFFF">
						<td width="20">&nbsp;</td>
						<td>
							<font style="font-family:sans-serif;font-size:12px"><?php echo rgar( $entry, '10' ); ?></font>
						</td>
					</tr>
					<tr bgcolor="#EAF2FA">
						<td colspan="2">
							<font style="font-family:sans-serif;font-size:12px"><strong>Additional Accessories</strong></font>
						</td>
					</tr>
					<tr bgcolor="#FFFFFF">
						<td width="20">&nbsp;</td>
						<td>
							<font style="font-family:sans-serif;font-size:12px"><?php echo rgar( $entry, '57' ); ?></font>
						</td>
					</tr>
				</tbody>
			</table>
		</td>
	</tr>
</table>
<!-- Price table -->
<table width="99%" border="0" cellpadding="1" cellspacing="0" bgcolor="#EAEAEA">
	<tr>
		<td>
			<table width="100%" border="0" cellpadding="5" cellspacing="0" bgcolor="#FFFFFF">
				<tbody>
					<tr>
						<td colspan="2" style="font-size:14px;font-weight:bold;background-color:#eee;border-bottom:1px solid #dfdfdf;padding:7px 7px">Quote Details:</td>
					</tr>
					<tr bgcolor="#EAF2FA">
						<td>
							<font style="font-family:sans-serif;font-size:12px">Selection</font>
						</td>
						<td>
							&nsbp;
						</td>
						<td>
							<font style="font-family:sans-serif;font-size:12px"><?php echo $prod_string; ?></font>
						</td>
					</tr>
					<tr bgcolor="#FFFFFF">
						<td>
							<font style="font-family:sans-serif;font-size:12px">Outdoor Unit</font>
						</td>
						<td>
							<font style="font-family:sans-serif;font-size:12px"><?php echo get_post_meta( $product_post_id, 'sqms-product-odu-model', true ); ?></font>
						</td>
						<td>
							<font style="font-family:sans-serif;font-size:12px"><?php echo get_post_meta( $product_post_id, 'sqms-product-odu-price', true ); ?></font>
						</td>
					</tr>
					<tr bgcolor="#EAF2FA">
						<td>
							<font style="font-family:sans-serif;font-size:12px">Coil Model</font>
						</td>
						<td>
							<font style="font-family:sans-serif;font-size:12px"><?php echo get_post_meta( $product_post_id, 'sqms-product-coil-model', true ); ?></font>
						</td>
						<td>
							<font style="font-family:sans-serif;font-size:12px"><?php echo get_post_meta( $product_post_id, 'sqms-product-coil-price', true ); ?></font>
						</td>
					</tr>
					<tr bgcolor="#FFFFFF">
						<td>
							<font style="font-family:sans-serif;font-size:12px">Furnace Model</font>
						</td>
						<td>
							<font style="font-family:sans-serif;font-size:12px"><?php echo get_post_meta( $product_post_id, 'sqms-product-furnace-model', true ); ?></font>
						</td>
						<td>
							<font style="font-family:sans-serif;font-size:12px"><?php echo get_post_meta( $product_post_id, 'sqms-product-furnace-price', true ); ?></font>
						</td>
					</tr>
					<tr bgcolor="#EAF2FA">
						<td>
							<font style="font-family:sans-serif;font-size:12px">Total Equipment</font>
						</td>
						<td>
							&nbsp;
						</td>
						<td>
							<font style="font-family:sans-serif;font-size:12px"><?php echo get_post_meta( $product_post_id, 'sqms-product-system-price', true ); ?></font>
						</td>
					</tr>
				</tbody>
			</table>
		</td>
	</tr>
</table>
<!-- performance table -->
<table width="99%" border="0" cellpadding="1" cellspacing="0" bgcolor="#EAEAEA">
	<tr>
		<td>
			<table width="100%" border="0" cellpadding="5" cellspacing="0" bgcolor="#FFFFFF">
				<tbody>
					<tr>
						<td colspan="2" style="font-size:14px;font-weight:bold;background-color:#eee;border-bottom:1px solid #dfdfdf;padding:7px 7px">Performance Data:</td>
					</tr>
					<tr bgcolor="#EAF2FA">
						<td>
							<font style="font-family:sans-serif;font-size:12px">Cooling BTU</font>
						</td>
						<td>
							&nbsp;
						</td>
						<td>
							<font style="font-family:sans-serif;font-size:12px"><?php echo get_post_meta( $product_post_id, 'sqms-product-cooling-btu', true ); ?></font>
						</td>
					</tr>
					<tr bgcolor="#FFFFFF">
						<td>
							<font style="font-family:sans-serif;font-size:12px">SEER</font>
						</td>
						<td>
							&nbsp;
						</td>
						<td>
							<font style="font-family:sans-serif;font-size:12px"><?php echo get_post_meta( $product_post_id, 'sqms-product-seer', true ); ?></font>
						</td>
					</tr>
					<tr bgcolor="#EAF2FA">
						<td>
							<font style="font-family:sans-serif;font-size:12px">EER</font>
						</td>
						<td>
							&nbsp;
						</td>
						<td>
							<font style="font-family:sans-serif;font-size:12px"><?php echo get_post_meta( $product_post_id, 'sqms-product-eer', true ); ?></font>
						</td>
					</tr>
					<tr bgcolor="#FFFFFF">
						<td>
							<font style="font-family:sans-serif;font-size:12px">AHRI</font>
						</td>
						<td>
							&nbsp;
						</td>
						<td>
							<font style="font-family:sans-serif;font-size:12px"><?php echo get_post_meta( $product_post_id, 'sqms-product-ahri', true ); ?></font>
						</td>
					</tr>
				</tbody>
			</table>
		</td>
	</tr>
</table>
<!-- application table -->
<table width="99%" border="0" cellpadding="1" cellspacing="0" bgcolor="#EAEAEA">
	<tr>
		<td>
			<table width="100%" border="0" cellpadding="5" cellspacing="0" bgcolor="#FFFFFF">
				<tbody>
					<tr>
						<td colspan="2" style="font-size:14px;font-weight:bold;background-color:#eee;border-bottom:1px solid #dfdfdf;padding:7px 7px">Application Data:</td>
					</tr>
					<tr bgcolor="#EAF2FA">
						<td>
							<font style="font-family:sans-serif;font-size:12px">Application</font>
						</td>
						<td>
							&nbsp;
						</td>
						<td>
							<font style="font-family:sans-serif;font-size:12px"><?php echo get_post_meta( $product_post_id, 'sqms-product-application', true ); ?></font>
						</td>
					</tr>
					<tr bgcolor="#FFFFFF">
						<td>
							<font style="font-family:sans-serif;font-size:12px">Voltage</font>
						</td>
						<td>
							&nbsp;
						</td>
						<td>
							<font style="font-family:sans-serif;font-size:12px"><?php echo get_post_meta( $product_post_id, 'sqms-product-odu-voltage', true ); ?></font>
						</td>
					</tr>
				</tbody>
			</table>
		</td>
	</tr>
</table>
