<?php
/*
    Plugin Name: Cards widget
    Plugin URI: #
    Description: Display a deck of cards
    Author: David Alvarado
    Version: 1.0
    Author URI: #
*/
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}
/**
 *	Function that instantiates the Widget
 */
function register_script(){
	wp_register_style('widget-cards', plugins_url('/css/widget-cards.css', __FILE__ ));
}
add_action( 'wp_enqueue_scripts', 'register_script' );

add_action('widgets_init', 'register_cards_widget');
function register_cards_widget(){
	register_widget( 'WP_Widget_Cards' );
}
/**
 *	Widget Class
 */
class  WP_Widget_Cards extends WP_Widget {

	public function __construct() {
		//Widget Builder
		parent::__construct(
			'cards',
			esc_html__('Cards from Cards API', 'Cards API'),
			array( 'description' => esc_html__('Display cards from cards API', 'Cards API'))
		);
	}

	public function widget( $args, $instance ) {
		//Widget content to be displayed on the Sidebar
		extract($args);

		$title          = apply_filters( 'widget_title', $instance['title'] );
		$cards_number  = (isset($instance['cards_number']) && is_numeric($instance['cards_number'])) ? esc_attr($instance['cards_number']) : 1;

		echo $before_widget;
		
			if ( ! empty( $title ) ){echo $before_title . $title . $after_title;}

			//Use of transient to not overload the API requests 
			if ( false === ( $response_results = get_transient( 'w-cards') ) ) {


					//URL for Draw a Card
					 
					$url = 'https://deckofcardsapi.com/api/deck/new/draw/';

					$arguments = array(
                        'format'  => 'json',
						//The count variable defines how many cards to draw from the deck
						'count'=> $cards_number
					);
					//Bind the final URL of the API endpoint with all the arguments we have collected
					$url_parameters = array();
		            foreach ($arguments as $key => $value){
		                $url_parameters[] = $key.'='.$value;
		            }

					$url .= '?'.implode('&', $url_parameters);
                    
                    //Save the requested data
					$response = file_get_contents($url);
                    
                    
					if ($response) {
                        $response = json_decode($response,true);
                        
                        if (is_array($response['success'])) 
                       
						//Array that will contain the data for the widget output
						$response_results = array();
						//The success atribute tells us if the request has been made correctly
						if ($response['success'] == 'true') {
							foreach ($response['cards'] as $card) {
								$response_results[$card['code']]['url']  = esc_url($card["image"]);
								$response_results[$card['code']]['alt']  = esc_attr($card["value"].' of '.$card["suit"]);
							}
							//Set the transient with the response_results

			                if ( ! empty( $response_results ) ) {
			                	$response_results = base64_encode( serialize( $response_results ) );
								set_transient( 'w-cards', $response_results, apply_filters( 'null_cards_cache_time', HOUR_IN_SECONDS * 2 ) );
			                }

						}

					} else {
						return new WP_Error( 'cards_error', esc_html__('Could not get data', 'Cards API') );
					}

				}
				//Generate image list
				if (!empty($response_results)) {
					$response_results =  unserialize( base64_decode( $response_results ) );
					wp_enqueue_style('widget-cards');
					$output = '';
					$output .= '<div class="fila">';
						foreach ($response_results as $card) {
							$output .= ' <div class="columna">';
								$output .= '<img src="'.$card['url'].'" alt="'.$card['alt'].'" />';
							$output .= '</div>';
						}
					$output .= '</div>';
					echo $output;
				}

			

		echo $after_widget;
	}

 	public function form( $instance ) {
		//generate widget options
 		$defaults = array(
 			'title'          => esc_html__('Cards from Cards API', 'Cards API'),
 			'cards_number'  => '1',
 			);

 		$instance = wp_parse_args((array) $instance, $defaults);

		?>
		<p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php echo esc_html__( 'Title:', 'Cards API' ); ?></label> 
			<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr($instance['title']); ?>" />
		</p>
				<p>
			<label for="<?php echo $this->get_field_id( 'cards_number' ); ?>"><?php echo esc_html__( 'Number of cards to show:', 'Cards API' ); ?></label> 
			<input class="widefat" id="<?php echo $this->get_field_id( 'cards_number' ); ?>" name="<?php echo $this->get_field_name( 'cards_number' ); ?>" type="text" value="<?php echo $instance['cards_number']; ?>" size="3" />
		</p>
		<?php
	}

	public function update( $new_instance, $old_instance ) {

		$instance = $old_instance;
		$instance['title']          = strip_tags( $new_instance['title'] );
		$instance['cards_number']  = strip_tags( $new_instance['cards_number'] );
		//delete transient on every call to update
		$transient_name = 'w-cards';
		delete_transient($transient_name);
		return $instance;
	}
}
