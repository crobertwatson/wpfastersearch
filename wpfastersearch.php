/**
 * Fix search results in admin to search by page title only
 * Otherwise the search is slow, greedy, and practically useless
 * in wp-admin and in the page parent selector on large sites
 *
 * @param String $search
 * @param Object $wp_query Object
 * @return void
 */
function __search_by_title_only( $search, $wp_query ) {
	global $wpdb;

	$enabled_post_types = array(
		'page',
		'tribe_events'
	);
	if ( empty( $wp_query->query_vars['post_type'] ) || ! in_array( $wp_query->query_vars['post_type'], $enabled_post_types ) ) {
		return $search; // skip processing - not an enabled post type
	}

	$q = $wp_query->query_vars;
	$n = ! empty( $q['exact'] ) ? '' : '%';

	$search    =
	$searchand = '';
	if ( empty( $q['search_terms'] ) ) {
		$q['search_terms'] = array();
	}
	foreach ( (array) $q['search_terms'] as $term ) {
		$term      = esc_sql( $term );
		$search   .= "{$searchand}($wpdb->posts.post_title LIKE '{$n}{$term}{$n}')";
		$searchand = ' AND ';
	}

	if ( ! empty( $search ) ) {
		$search = " AND ({$search}) ";
		if ( ! is_user_logged_in() ) {
			$search .= " AND ($wpdb->posts.post_password = '') ";
		}
	}

	return $search;
}
add_filter( 'posts_search', '__search_by_title_only', 1000, 2 );
