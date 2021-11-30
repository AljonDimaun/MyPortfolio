<!DOCTYPE html>
<html <?php language_attributes(); ?>>

	<head>
<?php

	wp_head();
?>
		<style>

			.wsf-preview {

				padding: 20px;
			}

			.wsf-preview-header h1 {

				font-size: 30px !important;
				font-weight: normal !important;
				margin-top: 0 !important;
				margin-left: 0 !important;
				margin-bottom: 20px !important;
				padding: 0 !important;
			}

		</style>

	</head>

	<body class="wsf-preview">

		<header class="wsf-preview-header">

			<h1><?php the_title(); ?></h1>

		</header>
<?php

	if(have_posts()) {

		while(have_posts()) {

			the_post();
?>
		<section class="wsf-preview-content">

			<?php the_content(); ?>

		</section>
<?php
		}
	}

	wp_footer();
?>
	</body>

</html>