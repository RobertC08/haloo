<?php
/**
 * Template part for displaying post navigation
 *
 * @package Shopwell
 */

$next_post = get_next_post();
$prev_post = get_previous_post();

if ( ! $next_post && ! $prev_post ) {
	return;
}
?>

<nav class="navigation post-navigation" role="navigation">
	<div class="nav-titles">
		<?php if ( $prev_post ) : ?>
			<a class="nav-previous" href="<?php echo esc_url( get_permalink( $prev_post ) ); ?>">
				<?php
				echo \Shopwell\Icon::get_svg( 'arrow-left-long' );
				esc_html_e( 'Previous Post', 'shopwell' );
				?>
			</a>
		<?php endif; ?>
		<?php if ( $next_post ) : ?>
			<a class="nav-next" href="<?php echo esc_url( get_permalink( $next_post ) ); ?>">
				<?php
				esc_html_e( 'Next Post', 'shopwell' );
				echo \Shopwell\Icon::get_svg( 'arrow-right-long' );
				?>
			</a>
		<?php endif; ?>
	</div>
	<div class="nav-links">
		<?php if ( $prev_post ) : ?>
			<div class="nav-previous">
				<a href="<?php echo esc_url( get_permalink( $prev_post ) ); ?>">
					<?php
					if ( has_post_thumbnail( $prev_post ) ) {
						echo get_the_post_thumbnail( $prev_post, 'shopwell-post-thumbnail-small' );
					}
					?>
					<span class="nav-title">
						<?php
						echo esc_html( $prev_post->post_title );
						printf( '<div class="meta meta-date">%s</div>', esc_html( get_the_date() ) );
						?>
					</span>
				</a>
			</div>
		<?php endif; ?>
		<?php if ( $next_post ) : ?>
			<div class="nav-next">
			<a href="<?php echo esc_url( get_permalink( $next_post ) ); ?>">
					<span class="nav-title">
						<?php
						echo esc_html( $next_post->post_title );
						printf( '<div class="meta meta-date">%s</div>', esc_html( get_the_date() ) );
						?>
					</span>
					<?php
					if ( has_post_thumbnail( $next_post ) ) {
						echo get_the_post_thumbnail( $next_post, 'shopwell-post-thumbnail-small' );
					}
					?>
				</a>
			</div>
		<?php endif; ?>
	</div>
</nav>
