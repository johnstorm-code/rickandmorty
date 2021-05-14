<?php 

/**
Rick and Morty is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 2 of the License, or
any later version.
 
Rick and Morty is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.
 
You should have received a copy of the GNU General Public License
along with learning. If not, see https://www.gnu.org/licenses/gpl-2.0.html
*/

/**
 * Plugin Name:       Rick and Morty
 * Plugin URI:        https://localhost/learn/wordpress/
 * Description:       Serves Rick and Morty images and quotes from APIs
 * Version:           1.0.0
 * Requires at least: 5.5.3
 * Requires PHP:      7.4.8
 * Author:            John Storm
 * Author URI:        https://localhost/learn/wordpress/
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       rickandmorty
 * Domain Path:       /languages
 */

/*
 * Rick and Morty APIs:
 *		Quotes - http://loremricksum.com/documentation/
 *		Images - https://rickandmortyapi.com/
 */

define('rickandmorty_quotes_api', "http://loremricksum.com/api/?paragraphs=1&quotes=1");
define('rickandmorty_character_api', "https://rickandmortyapi.com/api/character?page=");
 
 /************************** Helpers **************************/
if(!function_exists('printr')){
	function printr($var){
		echo '<pre>';
		print_r($var);
		echo '</pre>';
	}
}
function mywp_remote_get( $url, $args = array() ) {
	$resp = wp_remote_get($url, $args);
	switch( wp_remote_retrieve_response_code($resp) ) {
		case 200:
			$final = json_decode(wp_remote_retrieve_body($resp));
		break;
		case 400:
			$final = json_encode(array('error'=>'wp_remote_get() response 400.'));
		default:
			;
	}
	return $final;
}

/*************************** Shortcode **************************/ 
add_shortcode('rickandmorty', 'get_rickandmorty_characters');
function get_rickmorty_quotes_api() {
	$res = wp_remote_get(rickandmorty_quotes_api);
	$json = strstr($res['body'], '{');
	if($json == null){
		return;
	}
	$final = json_decode($json);
	return $final;
}
function get_rickandmorty_characters(){
	$pg = rand(1,33);
	$rnd = rand(1,19);
	$res = mywp_remote_get(rickandmorty_character_api . $pg);
	$html = generate_rickandmorty_character($res->results[$rnd]);
	echo $html;
}
function get_rickandmorty_episode_info($char){
	$episode = mywp_remote_get($char->episode[0]);
	$html = "<br/><br/>Debut: " . $episode->name . " <br/>[" . $episode->episode . " - " . $episode->air_date . "]";
	return $html;
}
function get_rickandmorty_quote(){
	$quote = get_rickmorty_quotes_api();
	$html = "<div class='myquote'>\"" . $quote->data[0] . "\"</div>";
	return $html;
}
function generate_rickandmorty_character($char){
		$html = "";
		$html .= "<div class='mycontainer'>";
		$html .= "<div class='myimage'><img src='" . $char->image . "'></div>";
		$html .= "<div class='mycontent'>";
		$html .= "<div class='mysection'>";
		$html .= "<div class='mytitle'>" . $char->name . "</div>";
		$html .= generate_status_html($char);
		$html .= "<br/><br/>" . $char->species . " " . $char->gender;
		if(isset($char->type) and !empty($char->type)){ $html .= "<br/>(" . $char->type . ")"; }
		if(isset($char->origin) and !empty($char->origin)){ $html .= "<br/><br/>Origin: " . $char->origin->name; }
		$html .= "<br/>Location: " . $char->location->name;
		$html .= get_rickandmorty_episode_info($char);
		$html .= "</div>";
		$html .= "</div>";
		$html .= get_rickandmorty_quote($char);
		$html .= "</div>";
		return $html;
}
function generate_status_html($char){
	switch($char->status){
		case 'Dead':
			$html = "<var style='color: red;'>" . $char->status . "</var>";
		break;
		case 'Alive':
			$html = "<var style='color: #12cc08;'>" . $char->status . "</var>";
		break;
		case 'unknown':
			$html = "<var style='color: #ababab;'>" . $char->status . "</var>";
		break;
	}
	return $html;
}

/*************************** Actions ***************************/
add_action( 'wp_enqueue_scripts', 'my_name_scripts' );
function my_name_scripts() {
    wp_enqueue_style( 'rickandmortycss', plugins_url("public/css/main.css", __FILE__));
}
 ?>