<?php
	/**
	 * Template name: Profiles - Edit profile
	 */
	Profiles::handleProfilePage();
?>
<?php get_header(); ?>
	<section>
		<?php Profiles::showMessage(); ?>
		<form action="" method="post">
			<?php
				$fields = Profiles::getProfileFields();
				if ($fields):
					foreach ($fields as $field):
			?>
				<label for="<?php echo $field->name; ?>"><?php echo $field->label; ?></label>
				<input type="<?php echo $field->type; ?>" name="<?php echo $field->name; ?>" id="<?php echo $field->name; ?>" value="<?php echo $field->value; ?>">
			<?php
					endforeach;
				endif;
			?>
			<button type="submit">Save</button>
			<br>
			<p><a href="<?php echo home_url('/'); ?>">Back to home</a></p>
		</form>
	</section>
<?php get_footer(); ?>