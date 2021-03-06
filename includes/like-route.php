<?php

add_action('rest_api_init', 'universityLikeRoutes');
function universityLikeRoutes() {
  register_rest_route('university/v1', 'manageLike', array(
    'methods' => 'POST',
    'callback' => 'createLike'
  ));
  register_rest_route('university/v1', 'manageLike', array(
    'methods' => 'DELETE',
    'callback' => 'deleteLike'
  ));
}

function createLike($data) {
  if (is_user_logged_in()) {
    $academic = sanitize_text_field($data['academicId']);

    $existQuery = new WP_Query(array(
      'author' => get_current_user_id(),
      'post_type' => 'like',
      'meta_query' => array(
        array(
          'key' => 'liked_academic_id',
          'compare' => '=',
          'value' => $academic
        )
      )
    ));

    // If like does not already exist (and ID number actually belongs to an academic), create new like post, else die and send 'invalid academic id'
    if ($existQuery->found_posts == 0 AND get_post_type($academic) == 'academic') {
      return wp_insert_post(array(
        'post_type' => 'like',
        'post_status' => 'publish',
        'post_title' => 'Second php test',
        // Adds WP custom fields, also known as meta fields
        'meta_input' => array(
          // Uses ACF field name for the key name
          'liked_academic_id' => $academic
        )
      ));
    } else {
      die('Invalid academic ID');
    }

  } else {
    die('Only logged in users can create a like.');
  }
}

function deleteLike($data) {
  // Get id of like post to be deleted
  $likeId = sanitize_text_field($data['like']);

  // Make sure post being deleted belongs to current user and is of type 'like'
  if(get_current_user_id() == get_post_field('post_author', $likeId) AND get_post_type($likeId) == 'like') {
    // true skips the trash stage
    wp_delete_post($likeId, true);
    return 'Like deleted.';
  } else {
    die('You do not have permission to delete that.');
  }
}