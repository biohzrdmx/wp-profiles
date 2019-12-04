<?php
	/**
	 * Template name: Profiles - Sign-in
	 */
	Profiles::handleSignInPage();
?>
<?php get_header(); ?>
	<section>
		<?php Profiles::showMessage(); ?>
		<form action="" method="post">
			<?php
				$fields = Profiles::getSignInFields();
				if ($fields):
					foreach ($fields as $field):
			?>
				<label for="<?php echo $field->name; ?>"><?php echo $field->label; ?></label>
				<input type="<?php echo $field->type; ?>" name="<?php echo $field->name; ?>" id="<?php echo $field->name; ?>" value="<?php echo $field->value; ?>">
			<?php
					endforeach;
				endif;
			?>
			<button type="submit">Continue</button>
			<br>
			<p>Forgot your password? <a href="<?php echo Profiles::getPermalink('recover'); ?>">Recover your account</a></p>
			<p>Don't have an account yet? <a href="<?php echo Profiles::getPermalink('register'); ?>">Register here</a></p>
		</form>
	</section>
<?php get_footer(); ?>