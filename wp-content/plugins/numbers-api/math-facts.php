<?php
/*
    Plugin Name:Math facts
    Plugin URI: #
    Description: Display math facts
    Author: David Alvarado
    Version: 1.0
    Author URI: #
*/
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}
session_start();
/*
 *  Bootstrap Styles and scripts
 */
wp_register_style( 'bootstrap.min', 'https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css' );
wp_enqueue_style('bootstrap.min');
//Bootstrap Scripts
wp_register_script( 'bootstrap.min', 'https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js' );
wp_enqueue_script('bootstrap.min');
//Google Jquery
wp_register_script( 'jquery.min', 'https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js' );
wp_enqueue_script('jquery.min');

add_action('admin_menu', 'setup_menu');
 
function setup_menu(){
    add_menu_page( 'Math Facts', 'Math Facts', 'manage_options', 'math-facts', 'math_init' );
}

function math_init(){
  
  math_handle_post();

    ?>
        <h1>Math Facts</h1>
        <form method="POST" >
              
              <label for="style">Style</label>
              <select class="form-control" id='style' name='style'>
                <option value= "card bg-light mb-3">Light</option>
                <option value= "card text-white bg-info mb-3">Info</option>
                <option value= "card text-white bg-primary mb-3">Primary</option>
              </select>
               <label for="number">Number of facts</label>
              <input type="number" class="form-control" id='number' name ='number' placeholder="2">
               
              <?php submit_button('Save') ?>
        </form>
    <?php
}
function math_handle_post(){
  $output =''; 
    if(isset($_POST['style']) and isset($_POST['number'])){
    $style = $_POST['style'];
    $number = $_POST['number'];
    $url = "http://numbersapi.com/random/math";
    $response_results = array();
    for ($i = 1; $i <= $number; $i++) {
      $response = file_get_contents($url);
      $response_results[$i] = $response ;
      }
  if (!empty($response_results)) {
    
    
    $output .= '<div class="container mt-4">';
    $output .= ' <div class="row">';
    for ($j = 1; $j <= count($response_results); $j++) {
      $output .= '<div class="col-auto mb-3">';
         $output .= '<div class="'.$style.'" style="max-width: 18rem;">';
         $output .= '<div class="card-header">Math Fact #'.$j.'</div>';
         $output .= '<div class="card-body">';
         $output .= '<h5 class="card-title"></h5> ';
         $output .= '<p class="card-text">'.$response_results[$j].'</p>';
         $output .= '</div>';
         $output .= '</div>';
         $output .= '</div>';
        }
    $output .= '</div>';
    $output .= '</div>';
      
  }
 
  }
      $_SESSION['result'] = $output;
}
function body_open_scripts() {
      echo $_SESSION['result'];
     
}
add_action( 'get_footer', 'body_open_scripts' );