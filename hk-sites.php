<?php

/*
Plugin Name: HK Sites
Plugin URI: https://henrikkensen.se
Version: 1.0
Author: Henrik Kensén
Description: Adds site URL to adminbar site swicher in multisite.
Author URI: https://henrikkensen.se
Network: true
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Text Domain: hksites
*/


if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

// Remove WordPress standard my sites admin bar.
add_action( 'admin_bar_menu', 'remove_from_admin_bar', 999 );
function remove_from_admin_bar( $wp_admin_bar ) {
    $wp_admin_bar->remove_node('my-sites');
}

// Recreate my sites admin bar, add blog url to title.
add_action( 'admin_bar_menu', 'menu_gdpr_admin', 999 );
function menu_gdpr_admin( $wp_admin_bar ) {

	// Don't show for logged out users or single site mode.
	if ( ! is_user_logged_in() || ! is_multisite() ) {
		return;
	}
	// Show only when the user has at least one site, or they're a super admin.
	if ( count( $wp_admin_bar->user->blogs ) < 1 && ! current_user_can( 'manage_network' ) ) {
		return;
	}

    $hk_sites_url = admin_url( 'my-sites.php' );
    if ( $wp_admin_bar->user->active_blog ) {
		$hk_sites_url = get_admin_url( $wp_admin_bar->user->active_blog->blog_id, 'my-sites.php' );
	}

	$wp_admin_bar->add_node(
		array(
			'id'    => 'hk-sites',
			'title' => __( 'My Sites' ),
			'href'  => $hk_sites_url,
		)
	);

    if ( current_user_can( 'manage_network' ) ) {
		$wp_admin_bar->add_group(
			array(
				'parent' => 'hk-sites',
				'id'     => 'hk-sites-super-admin',
			)
		);

		$wp_admin_bar->add_node(
			array(
				'parent' => 'hk-sites-super-admin',
				'id'     => 'network-admin',
				'title'  => __( 'Network Admin' ),
				'href'   => network_admin_url(),
			)
		);

		$wp_admin_bar->add_node(
			array(
				'parent' => 'network-admin',
				'id'     => 'network-admin-d',
				'title'  => __( 'Dashboard' ),
				'href'   => network_admin_url(),
			)
		);

		if ( current_user_can( 'manage_sites' ) ) {
			$wp_admin_bar->add_node(
				array(
					'parent' => 'network-admin',
					'id'     => 'network-admin-s',
					'title'  => __( 'Sites' ),
					'href'   => network_admin_url( 'sites.php' ),
				)
			);
		}

		if ( current_user_can( 'manage_network_users' ) ) {
			$wp_admin_bar->add_node(
				array(
					'parent' => 'network-admin',
					'id'     => 'network-admin-u',
					'title'  => __( 'Users' ),
					'href'   => network_admin_url( 'users.php' ),
				)
			);
		}

		if ( current_user_can( 'manage_network_themes' ) ) {
			$wp_admin_bar->add_node(
				array(
					'parent' => 'network-admin',
					'id'     => 'network-admin-t',
					'title'  => __( 'Themes' ),
					'href'   => network_admin_url( 'themes.php' ),
				)
			);
		}

		if ( current_user_can( 'manage_network_plugins' ) ) {
			$wp_admin_bar->add_node(
				array(
					'parent' => 'network-admin',
					'id'     => 'network-admin-p',
					'title'  => __( 'Plugins' ),
					'href'   => network_admin_url( 'plugins.php' ),
				)
			);
		}

		if ( current_user_can( 'manage_network_options' ) ) {
			$wp_admin_bar->add_node(
				array(
					'parent' => 'network-admin',
					'id'     => 'network-admin-o',
					'title'  => __( 'Settings' ),
					'href'   => network_admin_url( 'settings.php' ),
				)
			);
		}
	}

    // Add site links.
	$wp_admin_bar->add_group(
		array(
			'parent' => 'hk-sites',
			'id'     => 'hk-sites-list',
			'meta'   => array(
				'class' => current_user_can( 'manage_network' ) ? 'ab-sub-secondary' : '',
			),
		)
	);

	$show_site_icons = apply_filters( 'wp_admin_bar_show_site_icons', true );

    foreach ( (array) $wp_admin_bar->user->blogs as $blog ) {
		switch_to_blog( $blog->userblog_id );

		if ( true === $show_site_icons && has_site_icon() ) {
			$blavatar = sprintf(
				'<img class="blavatar" src="%s" srcset="%s 2x" alt="" width="16" height="16"%s />',
				esc_url( get_site_icon_url( 16 ) ),
				esc_url( get_site_icon_url( 32 ) ),
				( wp_lazy_loading_enabled( 'img', 'site_icon_in_toolbar' ) ? ' loading="lazy"' : '' )
			);
		} else {
			$blavatar = '<div class="blavatar"></div>';
		}

		$blogname = $blog->blogname;

		if ( ! $blogname ) {
			$blogname = preg_replace( '#^(https?://)?(www.)?#', '', get_home_url() );
		} else {
            $blogname .= '<span style="font-family: monospace; font-size: 0.875em; line-height: 1;"><br>'.preg_replace( '#^(https?://)?(www.)?#', '', get_home_url() ).'</span>';
        }

		$menu_id = 'blog-' . $blog->userblog_id;

		if ( current_user_can( 'read' ) ) {
			$wp_admin_bar->add_node(
				array(
					'parent' => 'hk-sites-list',
					'id'     => $menu_id,
					'title'  => $blavatar . $blogname,
					'href'   => admin_url(),
				)
			);

			$wp_admin_bar->add_node(
				array(
					'parent' => $menu_id,
					'id'     => $menu_id . '-d',
					'title'  => __( 'Dashboard' ),
					'href'   => admin_url(),
				)
			);
		} else {
			$wp_admin_bar->add_node(
				array(
					'parent' => 'hk-sites-list',
					'id'     => $menu_id,
					'title'  => $blavatar . $blogname,
					'href'   => home_url(),
				)
			);
		}

		if ( current_user_can( get_post_type_object( 'post' )->cap->create_posts ) ) {
			$wp_admin_bar->add_node(
				array(
					'parent' => $menu_id,
					'id'     => $menu_id . '-n',
					'title'  => get_post_type_object( 'post' )->labels->new_item,
					'href'   => admin_url( 'post-new.php' ),
				)
			);
		}

		if ( current_user_can( 'edit_posts' ) ) {
			$wp_admin_bar->add_node(
				array(
					'parent' => $menu_id,
					'id'     => $menu_id . '-c',
					'title'  => __( 'Manage Comments' ),
					'href'   => admin_url( 'edit-comments.php' ),
				)
			);
		}

		$wp_admin_bar->add_node(
			array(
				'parent' => $menu_id,
				'id'     => $menu_id . '-v',
				'title'  => __( 'Visit Site' ),
				'href'   => home_url( '/' ),
			)
		);

		restore_current_blog();
	}

}

function hksites_css() { ?>
	<style type='text/css'>

    #wpadminbar .quicklinks .menupop ul#wp-admin-bar-hk-sites-list li .ab-item {
        height: auto;
        line-height: 1.4;
        padding-top: 0.375rem;
        padding-bottom: 0.375rem;
    }
    #wpadminbar .menupop ul#wp-admin-bar-hk-sites-list li.hover > .ab-sub-wrapper {
        margin-top: -56px;
    }
	</style>
<?php }
add_action( 'admin_head', 'hksites_css' );
add_action( 'wp_head', 'hksites_css' );