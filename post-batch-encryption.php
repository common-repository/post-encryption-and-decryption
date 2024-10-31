<?php
/*
Plugin Name: Post Encryption And Decryption
Plugin URI: http://www.ludou.org/wordpress-plugin-post-encryption-and-decryption.html
Description: Helps you quickly encrypt or decrypt all posts of specific category or tag.帮助您快速加密解密某个分类或某个标签下的所有的文章
Version: 1.1
Author: Ludou
Author URI: http://www.ludou.org/
*/

load_plugin_textdomain("ludouept", "/wp-content/plugins/post-encryption-and-decryption/languages/");

function do_encrypt_action() {
	global $wpdb;
	
	$checkbox = $_POST['ept_post_status'];
	if ( empty($checkbox) ) {
		return __('Update Failed! Not select any post status.','ludouept');
	}
	
	
	if ($_POST['choose_type'] == 'encrypt') {
		if ( (empty($_POST['ept_psw']) || strlen($_POST['ept_psw']) > 20) ) {
			return __('Update Failed! Has not filled in the password, or password length greater than 20.','ludouept');
		}
		$sqlcmd = "UPDATE $wpdb->posts, $wpdb->term_relationships, $wpdb->term_taxonomy SET $wpdb->posts.post_password = '" . $_POST['ept_psw'] . "'";
	}
	else if ($_POST['choose_type'] == 'decrypt'){
		$sqlcmd = "UPDATE $wpdb->posts, $wpdb->term_relationships, $wpdb->term_taxonomy SET $wpdb->posts.post_password = ''";
	}
	
	$where = " WHERE $wpdb->posts.ID = $wpdb->term_relationships.object_id AND $wpdb->term_taxonomy.term_taxonomy_id = $wpdb->term_relationships.term_taxonomy_id AND $wpdb->posts.post_type = 'post'";
	

	if ( $_POST['category_tag'] == 'tag' ) {
		$where .= " AND $wpdb->term_taxonomy.taxonomy = 'post_tag' AND $wpdb->term_taxonomy.term_id = '".$_POST['tag_encryption']."'";
	}
	else if ( $_POST['category_tag'] == 'category' ){
		$where .= " AND $wpdb->term_taxonomy.taxonomy = 'category' AND $wpdb->term_taxonomy.term_id = '".$_POST['cat']."'";
	}
	
	$where .= " AND ($wpdb->posts.post_status = '" . $checkbox[0] . "'";
	$i = 1;
	while( $i < count($checkbox) )
	{
  	$where .= " OR $wpdb->posts.post_status = '" . $checkbox[$i] . "'";
  	$i++;
	}
	$where .= ")";
	$sqlcmd .= $where;
	
	$wpdb->query($sqlcmd);
	
	return __('Update successfully!','ludouept');
}

function encryption_options() {
	$args = array(
        'smallest' => 8, 'largest' => 22, 'unit' => 'pt', 'number' => 999,
        'format' => 'array', 'orderby' => 'name', 'order' => 'ASC',
        'exclude' => '', 'include' => '');

	$tags = get_tags($args);
	
	if($_POST['encrypt_hidden'] == 'Y') {
		if( empty($_POST['category_tag']) ) {
			$msg = __('Update Failed! Has not chosen category or tag.','ludouept');
		}
		else {
			$msg = do_encrypt_action();
		}
		echo "<div class=\"updated fade\" id=\"message\"><p><strong>$msg</strong></p></div>";
	}
?>
<div class="wrap">
	<h2><?php _e("Encryption And Decryption Options","ludouept"); ?></h2>
	<p><a href="http://www.ludou.org/"><?php _e("Posts Batch Encryption And Decryption ","ludouept"); ?></a>, <?php _e("helps you quickly encrypt or decrypt all posts of specific category or tag","ludouept"); ?></p>
	<div style="float:right;">
		<form action="https://www.paypal.com/cgi-bin/webscr" method="post">
			<input type="hidden" name="cmd" value="_s-xclick">
			<input type="hidden" name="hosted_button_id" value="EGNM8ZAZSDA6A">
			<input type="image" src="https://www.paypal.com/en_GB/i/btn/btn_donate_LG.gif" border="0" name="submit" alt="Donate！">
			<img alt="" border="0" src="https://www.paypal.com/zh_XC/i/scr/pixel.gif" width="1" height="1">
		</form>
	</div>
	<form method="post" action="<?php echo str_replace( '%7E', '~', $_SERVER['REQUEST_URI']); ?>">
		<input type="hidden" name="encrypt_hidden" value="Y" />
		<table class="form-table">
				<tr valign="top">
					<th><?php _e("Encryption Or Decryption","ludouept"); ?>:</th>
					<td><select name="choose_type" onclick="choose()">
							<option value="encrypt"><?php _e("Encryption","ludouept"); ?></option>
							<option value="decrypt"><?php _e("Decryption","ludouept"); ?></option>
						</select></td>
				</tr>
				<tr valign="top">
					<th><?php _e("encrypt or decrypt specific:","ludouept"); ?></th>
					<td><select name="category_tag" onclick="choose()" id="category_tag">
							<option value="0"><?php _e("Noting","ludouept"); ?></option>
							<option value="tag"><?php _e("Tag","ludouept"); ?></option>
							<option value="category"><?php _e("Category","ludouept"); ?></option>
						</select></td>
				</tr>
				<tr valign="top" id="ept_tag" style="display:none;">
					<th><?php _e("Tag to encrypt or decrypt:","ludouept"); ?></th>
					<td><select name="tag_encryption" >
							<?php if($tags) : foreach ($tags as $tag) {?>
							<option value="<?php echo $tag->term_id; ?>"><?php echo $tag->name; ?></option>
							<?php } else : ?>
							<option value=""><?php _e("No tag","ludouept"); ?></option>
							<?php endif; ?>
						</select></td>
				</tr>
				<tr valign="top"	id="ept_category" style="display:none;">
					<th><?php _e("Category to encrypt or decrypt:","ludouept"); ?></th>
					<td><?php wp_dropdown_categories('show_count=1&hierarchical=1'); ?></td>
				</tr>
				<tr valign="top" id="ept_post_status" style="display:none;">
					<th><?php _e("Post status:","ludouept"); ?></th>
					<td><label>
							<input name="ept_post_status[]" type="checkbox" value="publish" />
							<?php _e("Published","ludouept"); ?></label> 
						<label>
							<input name="ept_post_status[]" type="checkbox" value="private" />
							<?php _e("Privately","ludouept"); ?></label>
						<label>
							<input name="ept_post_status[]" type="checkbox" value="draft" />
							<?php _e("Draft","ludouept"); ?></label>
						<label>
							<input name="ept_post_status[]" type="checkbox" value="trash" />
							<?php _e("Trash","ludouept"); ?></label>
						<label>
							<input name="ept_post_status[]" type="checkbox" value="inherit" />
							<?php _e("Inherit","ludouept"); ?></label></td>
				</tr>
				<tr valign="top" id="ept_pw" style="display:none;">
					<th><?php _e("Set the Password","ludouept"); ?></th>
					<td><input type="text" name="ept_psw" maxlength="20" value="" /></td>
				</tr>
		</table>
		<p class="submit">
			<input type="submit" value="<?php _e("Update Options &raquo;","ludouept"); ?> &raquo;" />
		</p>
	</form>
</div>
<script type="text/javascript">
//<![CDATA[
function choose()
{	
	var thistype = document.getElementById('category_tag');
	var choose_type = document.getElementsByName('choose_type');
	var traget;
		
	if(thistype.value == 'category') {
		traget = document.getElementById('ept_category');
		traget.style.display = "";
		traget = document.getElementById('ept_tag');
		traget.style.display = "none";
	}
	else if(thistype.value == 'tag') {
		traget = document.getElementById('ept_tag');
		traget.style.display = "";
		traget = document.getElementById('ept_category');
		traget.style.display = "none";
	}
	else if(thistype.value == 0) {
		traget = document.getElementById('ept_tag');
		traget.style.display = "none";
		traget = document.getElementById('ept_category');
		traget.style.display = "none";
	}

	if(thistype.value == 'category' || thistype.value == 'tag')
		document.getElementById('ept_post_status').style.display = "";
	else
		document.getElementById('ept_post_status').style.display = "none";
				
	if (choose_type[0].value=='encrypt' && thistype.value != 0)
		document.getElementById('ept_pw').style.display = "";
	else
		document.getElementById('ept_pw').style.display = "none";
}
//]]>
</script>
<?php			
}

add_action('admin_menu', 'encryption_add_actions');

function encryption_add_actions() {
	if (function_exists('add_options_page')) {
		add_options_page( "Encryption Options", "Post Encryption And Decryption", 5, basename(__FILE__), "encryption_options");
	}
}
?>