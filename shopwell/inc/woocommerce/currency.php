<?php
/**
 * Hooks of Currency.
 *
 * @package Shopwell
 */

namespace Shopwell\WooCommerce;

use Shopwell\Icon;

use Shopwell\Helper;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Class of Wishlist template.
 */
class Currency {
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


	public static function currency_status() {
		$args = array();
		if ( class_exists( 'WooCommerce' ) && class_exists( 'WOOCS' ) ) {
			global $WOOCS;

			$currencies       = class_exists( 'WOOCS' ) ? $WOOCS->get_currencies() : array();
			$currencies       = apply_filters( 'woocs_active_currencies', $currencies );
			$current_currency = class_exists( 'WOOCS' ) ? $WOOCS->current_currency : '';
			$current_currency = apply_filters( 'woocs_current_currencies', $current_currency );
			$symbol_currency  = get_woocommerce_currency_symbol( $current_currency );

			if ( ! empty( $currencies ) ) {
				$args = array(
					'currencies'       => $currencies,
					'current_currency' => $current_currency,
					'symbol_currency'  => $symbol_currency,
				);
			}
		} elseif ( class_exists( '\Aelia\WC\CurrencySwitcher\WC_Aelia_CurrencySwitcher' ) ) {
			$settings_controller = \Aelia\WC\CurrencySwitcher\WC_Aelia_CurrencySwitcher::settings();

			$enabled_currencies = $settings_controller->get_enabled_currencies();
			$exchange_rates     = $settings_controller->get_exchange_rates();

			$woocommerce_currencies = get_woocommerce_currencies();
			$currencies             = array();
			foreach ( $exchange_rates as $currency => $fx_rate ) {
				// Display only Currencies supported by WooCommerce
				$currency_name = ! empty( $woocommerce_currencies[ $currency ] ) ? $woocommerce_currencies[ $currency ] : false;
				if ( ! empty( $currency_name ) ) {
					// Skip currencies that are not enabled
					if ( ! in_array( $currency, $enabled_currencies ) ) {
						continue;
					}

					// Display only currencies with a valid Exchange Rate
					if ( $fx_rate > 0 ) {
						$currencies[ $currency ] = $currency_name;
					}
				}
			}

			if ( ! empty( $currencies ) ) {
				$args = array(
					'currencies'       => $currencies,
					'current_currency' => \Aelia\WC\CurrencySwitcher\WC_Aelia_CurrencySwitcher::instance()->get_selected_currency(),
					'symbol_currency'  => '',
				);
			}
		}

		return $args;
	}

	public static function currency_switcher( $display = 'list' ) {
		if ( class_exists( 'WOOCS' ) ) {
			echo self::woocs_currency_switcher( $display );
		} elseif ( class_exists( '\Aelia\WC\CurrencySwitcher\WC_Aelia_CurrencySwitcher' ) ) {
			echo self::aelia_currency_switcher( $display );
		}
	}

	public static function woocs_currency_switcher( $display = 'list' ) {
		$args          = self::currency_status();
		$currency_list = array();

		if ( empty( $args ) ) {
			return;
		}

		$currencies       = $args['currencies'];
		$current_currency = $args['current_currency'];

		if ( $display == 'list' ) {
			foreach ( $currencies as $key => $currency ) {
				if ( $current_currency == $key ) {
					array_unshift(
						$currency_list,
						sprintf(
							'<li class="shopwell-currency__menu-item active"><a href="#" class="woocs_flag_view_item woocs_flag_view_item_current active" data-currency="%s">%s %s</a></li>',
							esc_attr( $currency['name'] ),
							esc_html( $currency['name'] ),
							\Shopwell\Icon::get_svg( 'check' )
						)
					);
				} else {
					$currency_list[] = sprintf(
						'<li class="shopwell-currency__menu-item"><a href="#" class="woocs_flag_view_item" data-currency="%s">%s %s</a></li>',
						esc_attr( $currency['name'] ),
						esc_html( $currency['name'] ),
						\Shopwell\Icon::get_svg( 'check' )
					);
				}
			}

			echo '<ul class="preferences-menu__item-child">';
				echo implode( "\n\t", $currency_list );
			echo '</ul>';
		} else {
			?>
			<label><?php esc_html_e( 'Currency', 'shopwell' ); ?></label>
			<select name="currency" id="shopwell_currency" class="currency_select preferences_select woocs_flag_view_item">
				<?php
				foreach ( $currencies as $key => $currency ) {
					echo '<option value="' . esc_attr( $key ) . '"' . selected( $current_currency, esc_attr( $key ), false ) . ' data-currency="'
					. esc_attr( $currency['description'] ) . '">' . esc_html( $currency['description'] ) . '</option>';
				}
				?>
			</select>
			<?php
		}
	}

	public static function aelia_currency_switcher( $display = 'list' ) {
		$args          = self::currency_status();
		$currency_list = array();

		if ( empty( $args ) ) {
			return;
		}

		$currencies       = $args['currencies'];
		$current_currency = $args['current_currency'];

		if ( $display == 'list' ) {
			foreach ( $currencies as $key => $currency ) {
				if ( $current_currency == $key ) {
					array_unshift(
						$currency_list,
						sprintf(
							'<button type="submit" name="aelia_cs_currency" value="%s" class="currency_button shopwell-button--text active">%s %s</button>',
							esc_attr( $key ),
							esc_html( $currency ),
							\Shopwell\Icon::get_svg( 'check' )
						)
					);
				} else {
					$currency_list[] = sprintf(
						'<button type="submit" name="aelia_cs_currency" value="%s" class="currency_button shopwell-button--text">%s %s</button>',
						esc_attr( $key ),
						esc_html( $currency ),
						\Shopwell\Icon::get_svg( 'check' )
					);
				}
			}

			echo '<form method="post" class="preferences-menu__item-child currency_switch_form">';
				echo implode( "\n\t", $currency_list );
			echo '</form>';
		} else {
			?>
			<label><?php esc_html_e( 'Currency', 'shopwell' ); ?></label>
			<select name="aelia_cs_currency" id="shopwell_currency" class="currency_select preferences_select woocs_flag_view_item">
				<?php
				foreach ( $currencies as $key => $currency ) {
					echo '<option value="' . esc_attr( $key ) . '"' . selected( $current_currency, esc_attr( $key ), false ) . ' data-currency="'
					. esc_attr( $key ) . '">' . esc_html( $currency ) . '</option>';
				}
				?>
			</select>
			<?php
		}
	}

	/**
	 * Get currencies
	 *
	 * @package Shopwell
	 *
	 * @since 1.0.0
	 *
	 * @param $items
	 */
	public static function get_currencies( $args, $flag = false ) {
		if ( class_exists( 'WOOCS' ) ) {
			global $WOOCS;

			$current_currency = class_exists( 'WOOCS' ) ? $WOOCS->current_currency : '';
			$current_currency = apply_filters( 'woocs_current_currencies', $current_currency );

			if ( empty( $current_currency ) ) {
				return $args;
			}
			$args         = ! $args ? array() : $args;
			$args['icon'] = 'currency';
			if ( $flag ) {
				$args['icon'] = 'language';
				$args['name'] = ! empty( $args['name'] ) ? $args['name'] . '<span class="divider">/</span>' . $current_currency : $current_currency;
			} else {
				$args['name'] = $current_currency;
			}
		} elseif ( class_exists( '\Aelia\WC\CurrencySwitcher\WC_Aelia_CurrencySwitcher' ) ) {
			$current_currency = \Aelia\WC\CurrencySwitcher\WC_Aelia_CurrencySwitcher::instance()->get_selected_currency();
			$args['icon']     = 'currency';
			if ( $flag ) {
				$args['icon'] = 'language';
				$args['name'] = ! empty( $args['name'] ) ? $args['name'] . '<span class="divider">/</span>' . $current_currency : $current_currency;
			} else {
				$args['name'] = $current_currency;
			}
		}

		return $args;
	}
}
