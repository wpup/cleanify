<?php

/**
 * Plugin Name: Cleanify
 * Description: Remove slug from custom post types
 * Author: Fredrik Forsmo
 * Author URI: http://forsmo.me
 * Version: 1.0.0
 * Plugin URI: https://github.com/frozzare/cleanify
 */

final class Cleanify {

  /**
   * The Cleanify class instance.
   *
   * @var object
   */

  private static $instance;

  /**
   * Get the Cleanify class instance.
   *
   * @return object
   */

  public static function instance() {
    if ( !isset( self::$instance ) ) {
      self::$instance = new static;
      self::$instance->setup_actions();
      self::$instance->setup_filters();
    }
    return self::$instance;
  }

  /**
   * Get post types.
   *
   * @return array
   */

  private function get_post_types() {
    $post_types = ['post'];
    $cpts = (array) apply_filters( 'cleanify/cpts', [] );

    foreach ( $cpts as $cpt ) {
      if ( is_string( $cpt ) ) {
        $post_types[] = $cpt;
      }
    }

    return array_merge( $post_types, ['page'] );
  }

  /**
   * Setup all actions.
   */

  private function setup_actions() {
    add_action( 'pre_get_posts', [$this, 'parse_request'] );
    add_action( 'template_redirect', [$this, 'redirect'] );
  }

  /**
   * Setup all filters.
   */

  private function setup_filters() {
    add_filter( 'post_type_link', [$this, 'remove_post_type_slug'], 10, 3 );
  }

  /**
   * Redirect to right permalink if the database
   * permalink don't match the server request uri.
   */

  public function redirect() {
    global $wp_query;

    if ( ! isset( $wp_query->post ) ) {
      return;
    }

    $post_types = $this->get_post_types();

    if ( ! in_array( $wp_query->post->post_type, $post_types ) ) {
      return;
    }

    $permalink = get_permalink( $wp_query->post->ID );
    $parts     = parse_url( $permalink );
    $req_uri   = $_SERVER['REQUEST_URI'];

    if ( $parts['path'] != $req_uri ) {
      wp_safe_redirect( $permalink );
      exit;
    }
  }

  /**
   * Remove post type slug.
   *
   * @param string $post_type
   * @param object $post
   * @param string $leavename
   *
   * @return string
   */

  public function remove_post_type_slug( $post_link, $post, $leavename ) {
    if ( empty( $post ) || $post->post_type === 'post' || $post->post_type === 'page' || $post->post_status != 'publish' ) {
      return $post_link;
    }

    $post_types = $this->get_post_types();

    if ( ! in_array( $post->post_type, $post_types ) ) {
      return $post_link;
    }

    return str_replace( '/' . $post->post_type . '/', '/', $post_link );
  }

  /**
   * Parse request and replace post types.
   *
   * @param object $query
   */

  public function parse_request( $query ) {
    if ( ! $query->is_main_query() ) {
      return;
    }

    if ( count( $query->query ) !== 2 || ! isset( $query->query['page'] ) ) {
      return;
    }

    if ( ! empty( $query->query['name'] ) ) {
      $query->set( 'post_type', $this->get_post_types() );
    }
  }

}

add_action( 'plugins_loaded', function () {
  return Cleanify::instance();
} );
