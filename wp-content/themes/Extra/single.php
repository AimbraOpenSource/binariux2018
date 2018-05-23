<?php get_header(); ?>
<div id="main-content">
	<div class="container">
		<div id="content-area" class="<?php extra_sidebar_class(); ?> clearfix">
			<div class="et_pb_extra_column_main">
				<?php
				if ( have_posts() ) :
					while ( have_posts() ) : the_post(); ?>
				<article id="post-<?php the_ID(); ?>" <?php post_class( 'module single-post-module' ); ?>>
					<div class="post-wrap">
						<h1 class="entry-title"><?php the_title(); ?></h1>
						<div class="post-content entry-content">
							<?php the_content(); ?>
							<?php
								wp_link_pages( array(
									'before' => '<div class="page-links">' . esc_html__( 'Pages:', 'extra' ),
									'after'  => '</div>',
								) );
							?>
						</div>
					</div><!-- /.post-wrap -->
				</article>
				<?php
					endwhile;
				else :
					?>
					<h2><?php esc_html_e( 'Post not found', 'extra' ); ?></h2>
					<?php
				endif;
				?>
			</div><!-- /.et_pb_extra_column.et_pb_extra_column_main -->

			<?php get_sidebar(); ?>

		</div> <!-- #content-area -->
	</div> <!-- .container -->
</div> <!-- #main-content -->

<?php get_footer();
