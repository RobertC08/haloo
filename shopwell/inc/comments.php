<?php
/**
 * Comment functions and definitions.
 *
 * @package Shopwell
 */

namespace Shopwell;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Comments initial
 */
class Comments {
	/**
	 * Instance
	 *
	 * @var $instance
	 */
	protected static $instance = null;

	/**
	 * Initiator
	 *
	 * @since 1.0.0
	 * @return object
	 */
	public static function instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Instantiate the object.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function __construct() {
		add_filter( 'comment_form_default_fields', array( $this, 'comment_form_fields' ) );
	}

	/**
	 * Custom fields comment form
	 *
	 * @since  1.0
	 *
	 * @return  array  $fields
	 */
	public function comment_form_fields() {
		global $commenter, $aria_req;

		$comment_author       = isset( $commenter['comment_author'] ) ? $commenter['comment_author'] : '';
		$comment_author_email = isset( $commenter['comment_author_email'] ) ? $commenter['comment_author_email'] : '';

		$fields = array(
			'author' => '<p class="comment-form-author"><span>' . esc_attr__( 'Name', 'shopwell' ) . '</span>
						<input id ="author" name="author" type="text" required value="' . esc_attr( $comment_author ) .
						'" size    ="30"' . $aria_req . ' /></p>',

			'email'  => '<p class="comment-form-email"><span>' . esc_attr__( 'Email', 'shopwell' ) . '</span>
			        	<input id ="email" name="email" type="email" required value="' . esc_attr( $comment_author_email ) .
						'" size    ="30"' . $aria_req . ' /></p>',

		);

		return $fields;
	}

	/**
	 * Comment callback function
	 *
	 * @since 1.0.0
	 *
	 * @param object $comment
	 * @param array  $args
	 * @param int    $depth
	 *
	 * @return string
	 */
	public static function shopwell_comment( $comment, $args, $depth ) {
		$GLOBALS['comment'] = $comment;
		extract( $args, EXTR_SKIP );

		$avatar = '';
		if ( $args['avatar_size'] != 0 ) {
			$avatar = get_avatar( $comment, $args['avatar_size'] );
		}

		$classes = get_comment_class( empty( $args['has_children'] ) ? '' : 'parent' );
		$classes = $classes ? implode( ' ', $classes ) : $classes;

		$comments = array(
			'comment_parent'      => 0,
			'comment_ID'          => get_comment_ID(),
			'comment_class'       => $classes,
			'comment_avatar'      => $avatar,
			'comment_author_link' => get_comment_author_link(),
			'comment_link'        => get_comment_link( get_comment_ID() ),
			'comment_date'        => get_comment_date(),
			'comment_time'        => get_comment_time(),
			'comment_approved'    => $comment->comment_approved,
			'comment_text'        => get_comment_text(),
			'comment_reply'       => get_comment_reply_link(
				array_merge(
					$args,
					array(
						'add_below' => 'comment',
						'depth'     => $depth,
						'max_depth' => $args['max_depth'],
					)
				)
			),

		);

		$comment = self::comment_template( $comments );

		echo ! empty( $comment ) ? $comment : '';
	}

	/**
	 * Comment Template function
	 *
	 * @since 1.0.0
	 *
	 * @param object $comment
	 *
	 * @return string
	 */
	public static function comment_template( $comments ) {
		$output    = array();
		$output[]  = sprintf( '<li id="comment-%s" class="%s">', esc_attr( $comments['comment_ID'] ), esc_attr( $comments['comment_class'] ) );
		$output[]  = sprintf( '<article id="div-comment-%s" class="comment-body">', $comments['comment_ID'] );
		$output [] = ! empty( $comments['comment_avatar'] ) ? sprintf(
			'<header class="comment-meta">' .
			'<div class="comment-author vcard">%s</div>' .
			'</header>',
			$comments['comment_avatar']
		) : '';
		$output[]  = '<div class="comment-content"><div class="comment-metadata">';
		$output[]  = sprintf( '<div class="fn">%s </div>', $comments['comment_author_link'] );
		/* translators: 1: comment date, 2: comment time. */
		$date      = sprintf( esc_html__( '%1$s at %2$s', 'shopwell' ), $comments['comment_date'], $comments['comment_time'] );
		$output[]  = sprintf( '<a href="%s" class="date">%s</a>', esc_url( $comments['comment_link'] ), $date );
		$output[]  = '</div>';
		if ( $comments['comment_approved'] == '0' ) {
			$output[] = sprintf( '<em class="comment-awaiting-moderation">%s</em>', esc_html__( 'Your comment is awaiting moderation.', 'shopwell' ) );
		} else {
			$output[] = $comments['comment_text'];
		}

		$output[] = '<div class="reply">';
		$output[] = $comments['comment_reply'];

		if ( current_user_can( 'edit_comment', $comments['comment_ID'] ) ) {
			$output[] = sprintf( '<a class="comment-edit-link" href="%s">%s</a>', esc_url( admin_url( 'comment.php?action=editcomment&amp;c=' ) . $comments['comment_ID'] ), esc_html__( 'Edit', 'shopwell' ) );
		}

		$output[] = '</div></div></article>';

		return implode( ' ', $output );
	}
}
