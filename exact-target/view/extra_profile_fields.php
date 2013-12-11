<h3>Exact Target</h3>

<table class="form-table">
	<tr>
		<th><label for="xt-subscriber-key">Subscriber Key</label></th>
		<td>
			<input type="text" name="xt-subscriber-key" id="xt-subscriber-key" value="<?php echo esc_attr( get_the_author_meta( 'xt-subscriber-key', $user->ID ) ); ?>" class="regular-text" /><br />
			<span class="description">Changing this value will change the subscriber in Exact Target that this user's data is paired with.<br>
				If a subscriber cannot be found by this subscriber key, then a new subscriber will be created in Exact Target.
			  </span>
		</td>
	</tr>
</table>