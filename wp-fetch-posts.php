<?php 
/**
* Plugin Name: Fetch Posts via API
* Plugin URI: https://github.com/ianfamero/wp-fetch-posts
* Description: Fetch posts (title and excerpts only) from another WordPress website via API
* Version: 1.0
* Author: Ian Benedict
* Author URI: https://github.com/ianfamero/wp-fetch-posts
**/

add_action('admin_menu', 'fetch_posts_plugin');

function fetch_posts_plugin() {
	add_options_page('Fetch Posts Options', 'Fetch Posts via API', 'manage_options', 'wp-fetch-posts', 'fetch_posts_options');
}
function fetch_posts_options() {
	if (!current_user_can('manage_options')) {
		wp_die( __('You do not have sufficient permissions to access this page.' ));
	}
  echo '<div class="wrap">';
    echo '<h1>Fetch Posts via API</h1>';
		echo '<p>Fetch posts (title and excerpts only) from another WordPress website via API</p>';
		echo '<p><b>Example API:</b><i> &ltwp-website-url&gt/wp-json/wp/v2/posts</i>';
		if (isset($_POST['fetch']) && check_admin_referer('fetch_posts')) {
			echo '<h2>' . getPosts() . ' new post(s) added</h2>';
    }
    echo '<form action="' . admin_url('options-general.php?page=wp-fetch-posts') . '" method="post">';
		wp_nonce_field('fetch_posts');
		echo 'API: <input type="text" name="api_url" />';
    echo '<input type="hidden" value="true" name="fetch" />';
    submit_button('Fetch Posts');
    echo '</form>';
    echo '</div>';
  echo '</div>';
}
function getPosts() {
	$api_url = $_POST['api_url'];
	$request = wp_remote_get($api_url);
	$body = wp_remote_retrieve_body($request);
	$datas = json_decode($body);
	$new_posts = 0;
  foreach($datas as $data) {
		$title = $data->title->rendered;
		$content = $data->content->rendered;
		$excerpt = substr(strip_tags($content), 0, 350);
		$excerpt = preg_replace('/\s+/', ' ', trim($excerpt));
		$link = $data->link;
		$post_date = $data->date;
		$post_date_gmt = $data->date_gmt;
		if (!get_page_by_title($title, OBJECT, 'post')) {
			$post_id = wp_insert_post(array(
				'post_title' => $title,
				'post_content' => $excerpt . ' <a href="'. $link .'" target="_blank">[Continue Reading...]</a>',
				'post_status' => 'publish',
				'post_date' => $post_date,
				'post_date_gmt' => $post_date_gmt,
			));
			$new_posts++;
		}
	}
	return $new_posts;
}
?>