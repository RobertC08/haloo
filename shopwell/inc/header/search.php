<?php
/**
 * Search functions and definitions.
 *
 * @package Shopwell
 */

namespace Shopwell\Header;

use Shopwell\Helper;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Search initial
 */
class Search {
	/**
	 * Instantiate the object.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function __construct() {
		add_filter( 'pre_get_posts', array( $this, 'query' ) );
	}

	/**
	 * Change query search.
	 *
	 * @since 1.0.0
	 *
	 * @param $query
	 * @return $query
	 */
	public function query( $query ) {
		if ( $_GET['post_type'] !== 'post' ) {
			return;
		}

		$post_type = $_GET['post_type'];
		$category  = $_GET['category_name'];

		if ( ! $post_type ) {
			$post_type = 'any';
		}
		if ( ! $category ) {
			$category = null;
		}

		if ( $query->is_search ) {
			$query->set( 'post_type', $post_type );
			$query->set( 'category_name', $category );
		}

		return $query;
	}

	/**
	 * Get search items.
	 *
	 * @since 1.0.0
	 *
	 * @param array $args
	 * @return void
	 */
	public static function items( $args ) {

		$search_items = $args['search_items'];

		if ( empty( $search_items ) ) {
			return;
		}

		foreach ( $search_items as $search_item => $item ) {
			echo get_template_part( 'template-parts/searchs/' . $item, '', $args );
		}
	}

	/**
	 * Get search type.
	 *
	 * @since 1.0.0
	 *
	 * @param string $type
	 * @return string
	 */
	public static function type( $type = 'post' ) {
		if ( Helper::get_option( 'header_search_type' ) !== 'adaptive' ) {
			$type = Helper::get_option( 'header_search_type' );
		} elseif ( Helper::is_blog() || is_singular( 'post' ) ) {
				$type = 'post';
		} else {
			$type = 'product';
		}

		if ( Helper::is_blog() || is_singular( 'post' ) ) {
			$type = 'post';
		}

		return $type;
	}

	/**
	 * Display trending searches links.
	 *
	 * @since 1.0.0
	 *
	 * @param array $args
	 * @return void
	 */
	public static function trendings( $args ) {
		if ( ! isset( $args['trending_searches'] ) ) {
			return;
		}

		if ( ! $args['trending_searches'] ) {
			return;
		}

		$trending_searches = apply_filters( 'shopwell_search_quicklinks', Helper::get_option( 'header_search_links' ) );

		if ( ! $trending_searches || empty( $trending_searches ) ) {
			return;
		}

		$post_type                  = Helper::get_option( 'header_search_type' );
		$trending_searches_position = isset( $args['trending_searches_position'] ) && ! empty( $args['trending_searches_position'] ) ? $args['trending_searches_position'] : 'outside';

		?>
		<div class="header-search__trending header-search__trending--<?php echo esc_attr( $trending_searches_position ); ?>">
			<div class="header-search__trending-label"><?php esc_html_e( 'Trending Searches', 'shopwell' ); ?></div>

			<ul class="header-search__trending-links">
				<?php
				foreach ( $trending_searches as $trending_search ) {
					$url = $trending_search['url'];

					if ( ! $url ) {
						$query = array( 's' => $trending_search['text'] );

						if ( $post_type ) {
							$query['post_type'] = $post_type;
						}

						$url = add_query_arg( $query, home_url( '/' ) );
					}

					printf(
						'<li><a href="%s">%s</a></li>',
						esc_url( $url ),
						esc_html( $trending_search['text'] )
					);
				}
				?>
			</ul>
		</div>
		<?php
	}

	/**
	 * Get category items.
	 *
	 * @since 1.0.0
	 *
	 * @param string $label
	 * @return void
	 */
	public static function categories_items( $label ) {
		if ( ! Helper::get_option( 'header_search_type' ) ) {
			return;
		}

		if ( Helper::get_option( 'header_search_type' ) == 'adaptive' ) {
			$type     = 'product';
			$taxonomy = 'product_cat';
		} else {
			$type     = Helper::get_option( 'header_search_type' );
			$taxonomy = ( Helper::get_option( 'header_search_type' ) === 'product' ) ? 'product_cat' : 'category';
		}

		$cats       = Helper::get_option( 'header_search_' . $type . '_cats' );
		$hide_empty = Helper::get_option( 'header_search_cats_empty' ) ? false : true;

		$args = array(
			'taxonomy'   => $taxonomy,
			'hide_empty' => $hide_empty,
		);

		if ( Helper::get_option( 'header_search_cats_top' ) ) {
			$args['parent'] = 0;
		}

		if ( is_numeric( $cats ) ) {
			$args['number'] = $cats;
		} elseif ( ! empty( $cats ) ) {
			$args['name']    = explode( ',', $cats );
			$args['orderby'] = 'include';
			unset( $args['parent'] );
		}

		$terms = get_terms( $args );
		if ( is_wp_error( $terms ) || empty( $terms ) ) {
			return;
		}
		$terms[]['all_categories'] = array(
			'slug' => '0',
			'name' => $label,
		);
		$rows                      = ceil( ( count( $terms ) ) / 3 );
		if ( count( $terms ) % 3 == 0 ) {
			$rows = $rows + 1;
		}
		$term_html = array();

		if ( $terms && ! is_wp_error( $terms ) ) :
			?>
			<div class="header-search__categories">
				<div class="header-search__categories-title"><span><?php echo esc_html__( 'Select Categories', 'shopwell' ); ?></span><?php echo \Shopwell\Icon::get_svg( 'close', 'ui', 'class=header-search__categories-close' ); ?></div>
				<ul class="header-search__categories-container" <?php printf( 'style="--shopwell-header-search-cats-rows: %s"', esc_attr( $rows ) ); ?>>
					<?php
					foreach ( $terms as $term ) :
						if ( ! empty( $term->slug ) ) {
							$term_html[] = '<li><a href="' . get_term_link( $term->slug, $taxonomy ) . '" data-slug="' . esc_attr( $term->slug ) . '">' . esc_html( $term->name ) . '</a></li>';
						} else {
							$term_html[] = '<li><a href="#" class="active" data-slug="0">' . esc_html( $label ) . '</a></li>';
						}
						endforeach;

						echo implode( '', $term_html );
					?>
				</ul>
			</div>
			<?php
		endif;
	}
}
