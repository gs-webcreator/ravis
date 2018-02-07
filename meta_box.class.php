<?php
	
	class Ravis_booking_meta_boxes
	{
		/**
		 * Array of meta data list for the events
		 * @var array
		 */
		public $meta_box_fields = array ();
		public $meta_box_title;
		public $meta_box_post_type;
		public $meta_box_context;
		public $meta_box_priority;
		public $meta_box_prefix;
		
		function __construct( $meta_items, $prefix, $title, $post_type, $context = 'normal', $priority = 'high' )
		{
			Ravis_booking_main::ravis_load_plugin_text_domain();
			$this->meta_box_fields    = $meta_items;
			$this->meta_box_title     = $title;
			$this->meta_box_post_type = $post_type;
			$this->meta_box_context   = $context;
			$this->meta_box_priority  = $priority;
			$this->meta_box_prefix    = $prefix;
			add_action( 'add_meta_boxes', array ( $this, 'add_meta_box' ) );
			add_action( 'save_post', array ( $this, 'save_meta_box' ) );
		}
		
		// Add the Meta Box
		function add_meta_box()
		{
			add_meta_box( $this->meta_box_prefix . $this->meta_box_post_type . '_meta_box', // ID
				$this->meta_box_title, // Title
				array ( $this, 'show_meta_box' ), // Callback
				$this->meta_box_post_type, // Post Type
				$this->meta_box_context, // Context
				$this->meta_box_priority // Priority
			);
		}
		
		// Show the Fields in the Post Type section
		function show_meta_box( $post )
		{
			global $post, $wpdb;
			
			// Use nonce for verification
			echo '<input type="hidden" name="' . $this->meta_box_post_type . '_meta_box_nonce" value="' . esc_attr( wp_create_nonce( basename( __FILE__ ) ) ) . '" />';
			
			// Begin the field table and loop
			echo '<table class="form-table">';
			foreach ( $this->meta_box_fields as $field )
			{
				// get value of this field if it exists for this post
				$meta = get_post_meta( $post->ID, $field['id'], true );
				// begin a table row with
				echo '<tr ' . ( ! empty( $field['tr_class'] ) ? 'class="' . esc_attr( $field['tr_class'] ) . '"' : '' ) . '>';
				if ( ! empty( $field['label'] ) )
				{
					echo '<th>' . esc_html( $field['label'] ) . '</th>';
				}
				echo '<td>';
				switch ( $field['type'] )
				{
					// Text
					case 'text':
						echo '<input type="text" name="' . esc_attr( $field['id'] ) . '" id="' . esc_attr( $field['id'] ) . '" value="' . esc_attr( $meta ) . '" size="40" />
                                    <br /><span class="description">' . balancetags( $field['desc'] ) . '</span>';
					break;
					
					// Number
					case 'number':
						echo '<input type="number" min="0" name="' . esc_attr( $field['id'] ) . '" id="' . esc_attr( $field['id'] ) . '" value="' . esc_attr( $meta ) . '" size="40" />
                                    <br /><span class="description">' . balancetags( $field['desc'] ) . '</span>';
					break;
					
					// Email
					case 'email':
						echo '<input type="email" name="' . esc_attr( $field['id'] ) . '" id="' . esc_attr( $field['id'] ) . '" value="' . esc_attr( $meta ) . '" size="40" />
                                    <br /><span class="description">' . balancetags( $field['desc'] ) . '</span>';
					break;
					
					// TextArea
					case 'textarea':
						echo '
						<textarea name="' . esc_attr( $field['id'] ) . '" id="' . esc_attr( $field['id'] ) . '">' . esc_attr( $meta ) . '</textarea>
                                    <br /><span class="description">' . balancetags( $field['desc'] ) . '</span>';
					break;
					
					// Select
					case 'select':
						echo '<select name="' . esc_attr( $field['id'] ) . '" id="' . esc_attr( $field['id'] ) . '">';
						foreach ( $field['options'] as $value => $title )
						{
							echo '<option value="' . esc_attr( $value ) . '" ' . selected( $meta, $value ) . '>' . esc_html( $title ) . '</option>';
						}
						echo '</select>
							<br /><span class="description">' . balancetags( $field['desc'] ) . '</span>';
					break;
					
					// Price
					case 'price':
						echo '<input type="number" name="' . esc_attr( $field['id'] ) . '" id="' . esc_attr( $field['id'] ) . '" value="' . esc_attr( $meta ) . '" size="40" placeholder="' . esc_html__( 'Price (number only)', 'ravis-booking' ) . '" class="price-field"  />
						<span class="price-separated-container"><span class="unit">$</span><span class="digit">' . esc_attr( ! empty( $meta ) ? number_format( $meta ) : '' ) . '</span></span>';
					break;
					
					// Area
					case 'area':
						if ( gettype( $meta ) === 'string' )
						{
							$meta = array (
								'value' => $meta,
								'unit'  => 'sqft'
							);
						}
						echo '<input type="number" min="0" name="' . esc_attr( $field['id'] ) . '[value]" id="' . esc_attr( $field['id'] ) . '[value]" value="' . esc_attr( $meta['value'] ) . '" size="40" />
								<select name="' . esc_attr( $field['id'] ) . '[unit]" id="' . esc_attr( $field['id'] ) . '[unit]">
									<option value="sqft" ' . selected( 'sqft', $meta['unit'], false ) . '>' . esc_html__( 'Square Foot (sqft)', 'ravis-booking' ) . '</option>
									<option value="m2" ' . selected( 'm2', $meta['unit'], false ) . '>' . esc_html__( 'Square Meter (m2)', 'ravis-booking' ) . '</option>
									<option value="acre" ' . selected( 'acre', $meta['unit'], false ) . '>' . esc_html__( 'Acre (acre)', 'ravis-booking' ) . '</option>
									<option value="ha" ' . selected( 'ha', $meta['unit'], false ) . '>' . esc_html__( 'Hectare (ha)', 'ravis-booking' ) . '</option>
									<option value="sqkm" ' . selected( 'sqkm', $meta['unit'], false ) . '>' . esc_html__( 'Square Kilometre (sqkm)', 'ravis-booking' ) . '</option>
									<option value="sqmi" ' . selected( 'sqmi', $meta['unit'], false ) . '>' . esc_html__( 'Square Mile (sqmi)', 'ravis-booking' ) . '</option>
									<option value="sqyd" ' . selected( 'sqyd', $meta['unit'], false ) . '>' . esc_html__( 'Square Yard (sqyd)', 'ravis-booking' ) . '</option>
								</select>
                                    <br /><span class="description">' . balancetags( $field['desc'] ) . '</span>';
					break;
					
					// Repeatable
					case 'repeatable':
						echo '
                            <ul id="' . esc_attr( $field['id'] ) . '-repeatable" class="custom_repeatable">';
						if ( $meta )
						{
							$i = 0;
							foreach ( $meta as $row )
							{
								echo '
									<li class="single">
			                            <div class="input-containers">
			                            	<div class="input-box"><input type="text" name="' . esc_attr( $field['id'] ) . '[' . esc_attr( $i ) . '][title]" value="' . esc_attr( $row['title'] ) . '" placeholder="' . esc_html__( 'Title', 'ravis-booking' ) . '" /></div>
			                            </div>
			                            <a class="repeatable-remove delete" href="#"><i class="dashicons dashicons-no"></i></a>
			                        </li>
                                    ';
								$i ++;
							}
						}
						echo '</ul>
						<a class="repeatable-add button button-primary button-large" href="#">' . esc_html__( 'Add New', 'ravis-booking' ) . '</a>
                        <ul class="li-tpml" style="display:none;">
                            <li class="single">
                                <div class="input-containers">
                                    <div class="input-box"><input type="text" name="" id="' . esc_attr( $field['id'] ) . '" data-name="title" value="" placeholder="' . esc_html__( 'Title', 'ravis-booking' ) . '" /></div>
                                </div>
                                <a class="repeatable-remove delete" href="#"><i class="dashicons dashicons-no"></i></a>
	                        </li>
                        </ul>
                        <span class="description">' . esc_html( $field['desc'] ) . '</span>';
					break;
					
					// Checkbox Group
					case 'checkbox_group':
						echo '
                                <ul class="list-inline">';
						if ( $field['options'] != '' )
						{
							foreach ( $field['options'] as $option )
							{
								echo '
                                        <li>
                                            <input type="checkbox" value="' . esc_attr( $option['value'] ) . '" name="' . esc_attr( $field['id'] ) . '[]" id="' . esc_attr( $option['value'] ) . '"', $meta && in_array( $option['value'], $meta ) ? esc_attr( ' checked="checked"' ) : '', ' />
                                            <label for="' . esc_attr( $option['value'] ) . '">' . esc_html( $option['label'] ) . '</label>
                                        </li>';
							}
						}
						echo '</ul><span class="description">' . esc_html( $field['desc'] ) . '</span></div>';
					break;
					
					// Date Field
					case 'date':
						echo '<input type="text" class="datepicker ' . esc_attr( ! empty( $field['class'] ) ? $field['class'] : '' ) . '" name="' . esc_attr( $field['id'] ) . '" id="' . esc_attr( $field['id'] ) . '" value="' . ( $meta ? esc_attr( $meta ) : "" ) . '" size="30" />
    									<span class="description">' . esc_html( $field['desc'] ) . '</span>';
					break;
					
					// Image
					case 'image':
						$img_info    = wp_get_attachment_thumb_url( $meta );
						$img_preview = ( ! empty( $img_info ) ? '<div class="image-preview-box"><img src="' . esc_url( $img_info ) . '"/></div>' : '' );
						
						echo '
							<div class="single-image-uploader">
								<div class="img-container">' . ( ! empty( $img_preview ) ? wp_kses_post( $img_preview ) : '' ) . '</div>
								<a class="add-image button button-primary button-large ' . ( ! empty( $img_preview ) ? esc_attr( 'hidden' ) : '' ) . '" href="#">' . esc_html__( 'Upload Image', 'ravis-booking' ) . '</a>
								<a class="remove-image button button-primary button-large ' . ( empty( $img_preview ) ? esc_attr( 'hidden' ) : '' ) . '" href="#">' . esc_html__( 'Remove Image', 'ravis-booking' ) . '</a>
								<input type="hidden" name="' . esc_attr( $field['id'] ) . '" value="' . esc_attr( $meta ) . '"/>
							</div>
						';
					break;
					
					// Gallery
					case 'gallery':
						
						echo '
                            <div class="ravis_slideshow_wrapper hide-if-no-js">
                                <ul class="slideshow_images clearfix">';
						
						$slideshow_images = get_post_meta( $post->ID, $field['id'], true );
						
						$attachments = array_filter( explode( '---', $slideshow_images ) );
						
						if ( $attachments )
						{
							foreach ( $attachments as $attachment_id )
							{
								$attachment_id = trim( $attachment_id );
								if ( ! empty( $attachment_id ) )
								{
									echo '<li class="image" data-attachment_id="' . esc_attr( $attachment_id ) . '">' . wp_get_attachment_image( $attachment_id, 'image' ) . '<a href="#" class="delete_slide" title="' . esc_attr( esc_html__( 'Delete image', 'ravis-booking' ) ) . '"><i class="dashicons dashicons-no"></i></a></li>';
								}
							}
						}
						echo '
                                </ul>
                                <input type="hidden" id="' . esc_attr( $field['id'] ) . '" name="' . esc_attr( $field['id'] ) . '" value="' . esc_attr( $slideshow_images ) . ' " />
                                <a href="#" class="add_slideshow_images button button-primary button-large">' . esc_html__( 'Add images', 'ravis-booking' ) . '</a>
                            </div>
                            ';
					break;
					
					// Capacity
					case 'capacity':
						echo '
						<div class="capacity-field-container">
							<input type="number" min="0" name="' . esc_attr( $field['id'] ) . '[main]" id="' . esc_attr( $field['id'] ) . '" value="' . esc_attr( ! empty( $meta['main'] ) ? $meta['main'] : '' ) . '" size="40" placeholder="' . esc_html__( 'Main', 'ravis-booking' ) . '" />
							+
							<input type="number" min="0" name="' . esc_attr( $field['id'] ) . '[extra]" id="' . esc_attr( $field['id'] ) . '" value="' . esc_attr( ! empty( $meta['extra'] ) ? $meta['extra'] : '' ) . '" size="40" placeholder="' . esc_html__( 'Extra', 'ravis-booking' ) . '" />
						</div>
                        <span class="description">' . balancetags( $field['desc'] ) . '</span>';
					break;
					
					// ID
					case 'id':
						echo '<div class="room-id-box">' . esc_html( $post->ID ) . '</div>';
					break;
					
					// Facility
					case 'facility':
						echo '
                            <ul id="' . $field['id'] . '-repeatable" class="custom_repeatable">';
						if ( $meta )
						{
							$i = 0;
							foreach ( $meta as $row )
							{
								echo '
									<li class="services">
			                            <div class="input-containers">
			                            	<div class="input-box"><input type="text" name="' . esc_attr( $field['id'] ) . '[' . esc_attr( $i ) . '][icon]" value="' . esc_attr( $row['icon'] ) . '" placeholder="' . esc_html__( 'Icon', 'ravis-booking' ) . '" /></div>
			                            	<div class="input-box"><input type="text" name="' . esc_attr( $field['id'] ) . '[' . esc_attr( $i ) . '][title]" value="' . esc_attr( $row['title'] ) . '" placeholder="' . esc_html__( 'Title', 'ravis-booking' ) . '" /></div>
			                            </div>
			                            <a class="repeatable-remove delete" href="#"><i class="dashicons dashicons-no"></i></a>
			                        </li>
                                    ';
								$i ++;
							}
						}
						echo '</ul>
						<a class="repeatable-add button button-primary button-large" href="#">' . esc_html__( 'Add New', 'ravis-booking' ) . '</a>
                        <ul class="li-tpml" style="display:none;">
                            <li class="services">
                                <div class="input-containers">
                                    <div class="input-box"><input type="text" name="" id="' . esc_attr( $field['id'] ) . '" data-name="icon" value="" placeholder="' . esc_html__( 'Icon', 'ravis-booking' ) . '" /></div>
                                    <div class="input-box"><input type="text" name="" id="' . esc_attr( $field['id'] ) . '" data-name="title" value="" placeholder="' . esc_html__( 'Title', 'ravis-booking' ) . '" /></div>
                                </div>
                                <a class="repeatable-remove delete" href="#"><i class="dashicons dashicons-no"></i></a>
	                        </li>
                        </ul>
                        <span class="description">' . esc_html( $field['desc'] ) . '</span>';
					break;
					
					// Service
					case 'service':
						echo '
                            <ul id="' . $field['id'] . '-repeatable" class="custom_repeatable">';
						if ( $meta )
						{
							$i = 0;
							foreach ( $meta as $row )
							{
								echo '
									<li class="services">
			                            <div class="input-containers">
			                            	<div class="input-box"><input type="text" name="' . esc_attr( $field['id'] ) . '[' . esc_attr( $i ) . '][title]" value="' . esc_attr( $row['title'] ) . '" placeholder="' . esc_html__( 'Title', 'ravis-booking' ) . '" /></div>
			                            	<div class="input-box"><input type="text" name="' . esc_attr( $field['id'] ) . '[' . esc_attr( $i ) . '][value]" value="' . esc_attr( $row['value'] ) . '" placeholder="' . esc_html__( 'Value', 'ravis-booking' ) . '" /></div>
			                            </div>
			                            <a class="repeatable-remove delete" href="#"><i class="dashicons dashicons-no"></i></a>
			                        </li>
                                    ';
								$i ++;
							}
						}
						echo '</ul>
						<a class="repeatable-add button button-primary button-large" href="#">' . esc_html__( 'Add New', 'ravis-booking' ) . '</a>
                        <ul class="li-tpml" style="display:none;">
                            <li class="services">
                                <div class="input-containers">
                                    <div class="input-box"><input type="text" name="" id="' . esc_attr( $field['id'] ) . '" data-name="title" value="" placeholder="' . esc_html__( 'Title', 'ravis-booking' ) . '" /></div>
                                    <div class="input-box"><input type="text" name="" id="' . esc_attr( $field['id'] ) . '" data-name="value" value="" placeholder="' . esc_html__( 'Value', 'ravis-booking' ) . '" /></div>
                                </div>
                                <a class="repeatable-remove delete" href="#"><i class="dashicons dashicons-no"></i></a>
	                        </li>
                        </ul>
                        <span class="description">' . esc_attr( $field['desc'] ) . '</span>';
					break;
					
					// Room Price
					case 'room_price':
						echo '
							<div class="base-room-price">
								<div class="row">
									<div class="price-box">
										<div class="title">' . esc_html__( 'Adult Weekday Price', 'ravis-booking' ) . '</div>
										<div class="input-container">
											<input type="number" min="0" name="' . esc_attr( $field['id'] ) . '[adult][weekday]" id="' . esc_attr( $field['id'] ) . '" value="' . esc_attr( ! empty( $meta['adult']['weekday'] ) ? $meta['adult']['weekday'] : '' ) . '" size="40" placeholder="' . esc_html__( 'Price (number only)', 'ravis-booking' ) . '" />
										</div>
									</div>
									<div class="price-box">
										<div class="title">' . esc_html__( 'Adult Weekend Price', 'ravis-booking' ) . '</div>
										<div class="input-container">
											<input type="number" min="0" name="' . esc_attr( $field['id'] ) . '[adult][weekend]" id="' . esc_attr( $field['id'] ) . '" value="' . esc_attr( ! empty( $meta['adult']['weekend'] ) ? $meta['adult']['weekend'] : '' ) . '" size="40" placeholder="' . esc_html__( 'Price (number only)', 'ravis-booking' ) . '" />
										</div>
									</div>
								</div>
								<div class="row">
									<div class="price-box">
										<div class="title">' . esc_html__( 'Child Weekday Price', 'ravis-booking' ) . '</div>
										<div class="input-container">
											<input type="number" min="0" name="' . esc_attr( $field['id'] ) . '[child][weekday]" id="' . esc_attr( $field['id'] ) . '" value="' . esc_attr( ! empty( $meta['child']['weekday'] ) ? $meta['child']['weekday'] : '' ) . '" size="40" placeholder="' . esc_html__( 'Price (number only)', 'ravis-booking' ) . '" />
										</div>
									</div>
									<div class="price-box">
										<div class="title">' . esc_html__( 'Child Weekend Price', 'ravis-booking' ) . '</div>
										<div class="input-container">
											<input type="number" min="0" name="' . esc_attr( $field['id'] ) . '[child][weekend]" id="' . esc_attr( $field['id'] ) . '" value="' . esc_attr( ! empty( $meta['child']['weekend'] ) ? $meta['child']['weekend'] : '' ) . '" size="40" placeholder="' . esc_html__( 'Price (number only)', 'ravis-booking' ) . '" />
										</div>
									</div>
								</div>
								<div class="description">' . balancetags( $field['desc'] ) . '</div>
							</div>
						';
					break;
					
					// Seasonal Price
					case 'seasonal_room_price':
						echo '
                            <ul id="' . $field['id'] . '-repeatable" class="custom_repeatable">';
						if ( $meta )
						{
							$i = 0;
							foreach ( $meta as $row )
							{
								echo '
									<li class="seasonal">
										<div class="base-room-price">
											<div class="row">
												<div class="price-box">
													<div class="title">' . esc_html__( 'Start Date', 'ravis-booking' ) . '</div>
													<div class="input-container">
														<input readonly type="text" name="' . esc_attr( $field['id'] ) . '[' . esc_attr( $i ) . '][start]" class="datepicker from" data-name="start" id="' . esc_attr( $field['id'] . '_' . $i . '_adult_start' ) . '" value="' . esc_attr( ! empty( $row['start'] ) ? $row['start'] : '' ) . '" size="40" placeholder="' . esc_html__( 'Start Date', 'ravis-booking' ) . '" />
													</div>
												</div>
												<div class="price-box">
													<div class="title">' . esc_html__( 'End Date', 'ravis-booking' ) . '</div>
													<div class="input-container">
														<input readonly type="text" name="' . esc_attr( $field['id'] ) . '[' . esc_attr( $i ) . '][end]" class="datepicker to" data-name="end" id="' . esc_attr( $field['id'] . '_' . $i . '_adult_end' ) . '" value="' . esc_attr( ! empty( $row['end'] ) ? $row['end'] : '' ) . '" size="40" placeholder="' . esc_html__( 'End Date', 'ravis-booking' ) . '" />
													</div>
												</div>
											</div>
											<div class="row">
												<div class="price-box">
													<div class="title">' . esc_html__( 'Adult Weekday Price', 'ravis-booking' ) . '</div>
													<div class="input-container">
														<input type="number" min="0" name="' . esc_attr( $field['id'] ) . '[' . esc_attr( $i ) . '][adult][weekday]" class="multiple-name" data-fname="adult" data-sname="weekday" id="' . esc_attr( $field['id'] . '_' . $i . '_adult_weekday' ) . '" value="' . esc_attr( ! empty( $row['adult']['weekday'] ) ? $row['adult']['weekday'] : '' ) . '" size="40" placeholder="' . esc_html__( 'Price (number only)', 'ravis-booking' ) . '" />
													</div>
												</div>
												<div class="price-box">
													<div class="title">' . esc_html__( 'Adult Weekend Price', 'ravis-booking' ) . '</div>
													<div class="input-container">
														<input type="number" min="0" name="' . esc_attr( $field['id'] ) . '[' . esc_attr( $i ) . '][adult][weekend]" class="multiple-name" data-fname="adult" data-sname="weekend" id="' . esc_attr( $field['id'] . '_' . $i . '_adult_weekend' ) . '" value="' . esc_attr( ! empty( $row['adult']['weekend'] ) ? $row['adult']['weekend'] : '' ) . '" size="40" placeholder="' . esc_html__( 'Price (number only)', 'ravis-booking' ) . '" />
													</div>
												</div>
											</div>
											<div class="row">
												<div class="price-box">
													<div class="title">' . esc_html__( 'Child Weekday Price', 'ravis-booking' ) . '</div>
													<div class="input-container">
														<input type="number" min="0" name="' . esc_attr( $field['id'] ) . '[' . esc_attr( $i ) . '][child][weekday]" class="multiple-name" data-fname="child" data-sname="weekday" id="' . esc_attr( $field['id'] . '_' . $i . '_child_weekday' ) . '" value="' . esc_attr( ! empty( $row['child']['weekday'] ) ? $row['child']['weekday'] : '' ) . '" size="40" placeholder="' . esc_html__( 'Price (number only)', 'ravis-booking' ) . '" />
													</div>
												</div>
												<div class="price-box">
													<div class="title">' . esc_html__( 'Child Weekend Price', 'ravis-booking' ) . '</div>
													<div class="input-container">
														<input type="number" min="0" name="' . esc_attr( $field['id'] ) . '[' . esc_attr( $i ) . '][child][weekend]" class="multiple-name" data-fname="child" data-sname="weekend" id="' . esc_attr( $field['id'] . '_' . $i . '_child_weekend' ) . '" value="' . esc_attr( ! empty( $row['child']['weekend'] ) ? $row['child']['weekend'] : '' ) . '" size="40" placeholder="' . esc_html__( 'Price (number only)', 'ravis-booking' ) . '" />
													</div>
												</div>
											</div>
											<div class="extra-box">
												<div class="title">' . esc_html__( 'Extra Guest Price', 'ravis-booking' ) . '</div>
												<div class="row">
													<div class="price-box">
														<div class="title">' . esc_html__( 'Adult Weekday Price', 'ravis-booking' ) . '</div>
														<div class="input-container">
															<input type="number" min="0" name="' . esc_attr( $field['id'] ) . '[' . esc_attr( $i ) . '][extra][adult][weekday]" class="multiple-name" data-fname="adult" data-sname="weekday" id="' . esc_attr( $field['id'] . '_' . $i . '_extra_adult_weekday' ) . '" value="' . esc_attr( ! empty( $row['extra']['adult']['weekday'] ) ? $row['extra']['adult']['weekday'] : '' ) . '" size="40" placeholder="' . esc_html__( 'Price (number only)', 'ravis-booking' ) . '" />
														</div>
													</div>
													<div class="price-box">
														<div class="title">' . esc_html__( 'Adult Weekend Price', 'ravis-booking' ) . '</div>
														<div class="input-container">
															<input type="number" min="0" name="' . esc_attr( $field['id'] ) . '[' . esc_attr( $i ) . '][extra][adult][weekend]" class="multiple-name" data-fname="adult" data-sname="weekend" id="' . esc_attr( $field['id'] . '_' . $i . '_extra_adult_weekend' ) . '" value="' . esc_attr( ! empty( $row['extra']['adult']['weekend'] ) ? $row['extra']['adult']['weekend'] : '' ) . '" size="40" placeholder="' . esc_html__( 'Price (number only)', 'ravis-booking' ) . '" />
														</div>
													</div>
												</div>
												<div class="row">
													<div class="price-box">
														<div class="title">' . esc_html__( 'Child Weekday Price', 'ravis-booking' ) . '</div>
														<div class="input-container">
															<input type="number" min="0" name="' . esc_attr( $field['id'] ) . '[' . esc_attr( $i ) . '][extra][child][weekday]" class="multiple-name" data-fname="child" data-sname="weekday" id="' . esc_attr( $field['id'] . '_' . $i . '_extra_child_weekday' ) . '" value="' . esc_attr( ! empty( $row['extra']['child']['weekday'] ) ? $row['extra']['child']['weekday'] : '' ) . '" size="40" placeholder="' . esc_html__( 'Price (number only)', 'ravis-booking' ) . '" />
														</div>
													</div>
													<div class="price-box">
														<div class="title">' . esc_html__( 'Child Weekend Price', 'ravis-booking' ) . '</div>
														<div class="input-container">
															<input type="number" min="0" name="' . esc_attr( $field['id'] ) . '[' . esc_attr( $i ) . '][extra][child][weekend]" class="multiple-name" data-fname="child" data-sname="weekend" id="' . esc_attr( $field['id'] . '_' . $i . '_extra_child_weekend' ) . '" value="' . esc_attr( ! empty( $row['extra']['child']['weekend'] ) ? $row['extra']['child']['weekend'] : '' ) . '" size="40" placeholder="' . esc_html__( 'Price (number only)', 'ravis-booking' ) . '" />
														</div>
													</div>
												</div>
											</div>
										</div>
			                            <a class="repeatable-remove delete" href="#"><i class="dashicons dashicons-no"></i></a>
			                        </li>
                                    ';
								$i ++;
							}
						}
						echo '</ul>
						<a class="repeatable-add button button-primary button-large" href="#">' . esc_html__( 'Add New', 'ravis-booking' ) . '</a>
                        <ul class="li-tpml" style="display:none;">
                            <li class="seasonal">
                            	<div class="base-room-price">
									<div class="row">
										<div class="price-box">
											<div class="title">' . esc_html__( 'Start Date', 'ravis-booking' ) . '</div>
											<div class="input-container">
												<input readonly type="text" name="" class="from" data-name="start" id="' . esc_attr( $field['id'] ) . '" data-id="' . esc_attr( $field['id'] . '_{{id}}_adult_start' ) . '" value="" size="40" placeholder="' . esc_html__( 'Start Date', 'ravis-booking' ) . '" />
											</div>
										</div>
										<div class="price-box">
											<div class="title">' . esc_html__( 'End Date', 'ravis-booking' ) . '</div>
											<div class="input-container">
												<input readonly type="text" name="" class="to" data-name="end" id="' . esc_attr( $field['id'] ) . '" data-id="' . esc_attr( $field['id'] . '_{{id}}_adult_end' ) . '" value="" size="40" placeholder="' . esc_html__( 'End Date', 'ravis-booking' ) . '" />
											</div>
										</div>
									</div>
									<div class="row">
										<div class="price-box">
											<div class="title">' . esc_html__( 'Adult Weekday Price', 'ravis-booking' ) . '</div>
											<div class="input-container">
												<input type="number" min="0" name="" class="multiple-name" data-fname="adult" data-sname="weekday" id="' . esc_attr( $field['id'] ) . '" value="" size="40" placeholder="' . esc_html__( 'Price (number only)', 'ravis-booking' ) . '" />
											</div>
										</div>
										<div class="price-box">
											<div class="title">' . esc_html__( 'Adult Weekend Price', 'ravis-booking' ) . '</div>
											<div class="input-container">
												<input type="number" min="0" name="" class="multiple-name" data-fname="adult" data-sname="weekend" id="' . esc_attr( $field['id'] ) . '" value="" size="40" placeholder="' . esc_html__( 'Price (number only)', 'ravis-booking' ) . '" />
											</div>
										</div>
									</div>
									<div class="row">
										<div class="price-box">
											<div class="title">' . esc_html__( 'Child Weekday Price', 'ravis-booking' ) . '</div>
											<div class="input-container">
												<input type="number" min="0" name="" class="multiple-name" data-fname="child" data-sname="weekday" id="' . esc_attr( $field['id'] ) . '" value="" size="40" placeholder="' . esc_html__( 'Price (number only)', 'ravis-booking' ) . '" />
											</div>
										</div>
										<div class="price-box">
											<div class="title">' . esc_html__( 'Child Weekend Price', 'ravis-booking' ) . '</div>
											<div class="input-container">
												<input type="number" min="0" name="" class="multiple-name" data-fname="child" data-sname="weekend" id="' . esc_attr( $field['id'] ) . '" value="" size="40" placeholder="' . esc_html__( 'Price (number only)', 'ravis-booking' ) . '" />
											</div>
										</div>
									</div>
									<div class="extra-box">
										<div class="title">' . esc_html__( 'Extra Guest Price', 'ravis-booking' ) . '</div>
										<div class="row">
											<div class="price-box">
												<div class="title">' . esc_html__( 'Adult Weekday Price', 'ravis-booking' ) . '</div>
												<div class="input-container">
													<input type="number" min="0" name="" class="multiple-name" data-prefix="extra" data-fname="adult" data-sname="weekday" id="' . esc_attr( $field['id'] ) . '" value="" size="40" placeholder="' . esc_html__( 'Price (number only)', 'ravis-booking' ) . '" />
												</div>
											</div>
											<div class="price-box">
												<div class="title">' . esc_html__( 'Adult Weekend Price', 'ravis-booking' ) . '</div>
												<div class="input-container">
													<input type="number" min="0" name="" class="multiple-name" data-prefix="extra" data-fname="adult" data-sname="weekend" id="' . esc_attr( $field['id'] ) . '" value="" size="40" placeholder="' . esc_html__( 'Price (number only)', 'ravis-booking' ) . '" />
												</div>
											</div>
										</div>
										<div class="row">
											<div class="price-box">
												<div class="title">' . esc_html__( 'Child Weekday Price', 'ravis-booking' ) . '</div>
												<div class="input-container">
													<input type="number" min="0" name="" class="multiple-name" data-prefix="extra" data-fname="child" data-sname="weekday" id="' . esc_attr( $field['id'] ) . '" value="" size="40" placeholder="' . esc_html__( 'Price (number only)', 'ravis-booking' ) . '" />
												</div>
											</div>
											<div class="price-box">
												<div class="title">' . esc_html__( 'Child Weekend Price', 'ravis-booking' ) . '</div>
												<div class="input-container">
													<input type="number" min="0" name="" class="multiple-name" data-prefix="extra" data-fname="child" data-sname="weekend" id="' . esc_attr( $field['id'] ) . '" value="" size="40" placeholder="' . esc_html__( 'Price (number only)', 'ravis-booking' ) . '" />
												</div>
											</div>
										</div>
									</div>
								</div>
                                <a class="repeatable-remove delete" href="#"><i class="dashicons dashicons-no"></i></a>
	                        </li>
                        </ul>
                        <span class="description">' . esc_html( $field['desc'] ) . '</span>';
					break;
					
					// Price Discount
					case 'price_discount':
						echo '
                            <ul id="' . $field['id'] . '-repeatable" class="custom_repeatable">';
						if ( $meta )
						{
							$i = 0;
							foreach ( $meta as $row )
							{
								echo '
									<li class="services">
			                            <div class="input-containers">
			                            	<div class="input-box"><input type="text" name="' . esc_attr( $field['id'] ) . '[' . esc_attr( $i ) . '][night]" value="' . esc_attr( $row['night'] ) . '" placeholder="' . esc_html__( 'Night', 'ravis-booking' ) . '" /></div>
			                            	<div class="input-box"><input type="text" name="' . esc_attr( $field['id'] ) . '[' . esc_attr( $i ) . '][percent]" value="' . esc_attr( $row['percent'] ) . '" placeholder="%" /></div>
			                            </div>
			                            <a class="repeatable-remove delete" href="#"><i class="dashicons dashicons-no"></i></a>
			                        </li>
                                    ';
								$i ++;
							}
						}
						echo '</ul>
						<a class="repeatable-add button button-primary button-large" href="#">' . esc_html__( 'Add New', 'ravis-booking' ) . '</a>
                        <ul class="li-tpml" style="display:none;">
                            <li class="services">
                                <div class="input-containers">
                                    <div class="input-box"><input type="text" name="" id="' . esc_attr( $field['id'] ) . '" data-name="night" value="" placeholder="' . esc_html__( 'Night', 'ravis-booking' ) . '" /></div>
                                    <div class="input-box"><input type="text" name="" id="' . esc_attr( $field['id'] ) . '" data-name="percent" value="" placeholder="%" /></div>
                                </div>
                                <a class="repeatable-remove delete" href="#"><i class="dashicons dashicons-no"></i></a>
	                        </li>
                        </ul>
                        <span class="description">' . esc_attr( $field['desc'] ) . '</span>';
					break;
					
					// Switch
					case 'switch':
						$post_status = get_post_status( $post->ID );
						$default     = null;
						
						if ( $post_status === 'auto-draft' && ! empty( $field['default'] ) )
						{
							$default = true;
						}
						elseif ( $meta === 'on' )
						{
							$default = true;
						}
						echo '
							<label class="ravis-booking-switch">
								<input name="' . esc_attr( $field['id'] ) . '" id="' . esc_attr( $field['id'] ) . '" ' . checked( $default, true, false ) . ' type="checkbox">
								<span class="switcher"></span>
							</label>
							<span class="description">' . balancetags( $field['desc'] ) . '</span>';
					break;
					
					// Menu Items
					case 'menu_items':
						echo '
                            <ul id="' . $field['id'] . '-repeatable" class="custom_repeatable">';
						if ( $meta )
						{
							$chief_select = ! empty( $meta['chief_select'] ) ? $meta['chief_select'] : '';
							
							$i = 0;
							foreach ( $meta['items'] as $row )
							{
								if ( ! empty( $row['title'] ) || ! empty( $row['price'] ) )
								{
									echo '
									<li class="menu-item">
			                            <div class="input-containers">
			                            	<div class="input-box chief-selection">
												<label>
													<input type="radio" name="' . esc_attr( $field['id'] ) . '[chief_select]" value="' . esc_attr( $i ) . '" ' . ( $i == $chief_select ? 'checked="checked"' : '' ) . ' />
													<span class="bg"><i class="dashicons dashicons-yes"></i></span>
													<span class="text">' . esc_html__( 'Chef Selection', 'ravis-booking' ) . '</span>
												</label>
			                            	</div>
			                            	<div class="input-box title-box"><input type="text" name="' . esc_attr( $field['id'] ) . '[items][' . esc_attr( $i ) . '][title]" value="' . esc_attr( $row['title'] ) . '" placeholder="' . esc_html__( 'Title', 'ravis-booking' ) . '" /></div>
			                            	<div class="input-box price-box"><input type="number" name="' . esc_attr( $field['id'] ) . '[items][' . esc_attr( $i ) . '][price]" step="any" value="' . esc_attr( $row['price'] ) . '" placeholder="' . esc_html__( 'Price (number only)', 'ravis-booking' ) . '" /></div>
			                            </div>
			                            <a class="repeatable-remove delete" href="#"><i class="dashicons dashicons-no"></i></a>
			                        </li>
                                    ';
								}
								$i ++;
							}
						}
						echo '</ul>
						<a class="menu-item-add button button-primary button-large" href="#">' . esc_html__( 'Add New', 'ravis-booking' ) . '</a>
                        <ul class="li-tpml" style="display:none;">
                            <li class="menu-item">
                                <div class="input-containers">
                                	<div class="input-box chief-selection">
										<label>
											<input type="radio" name="' . $field['id'] . '[chief_select]" value="" />
											<span class="bg"><i class="dashicons dashicons-yes"></i></span>
											<span class="text">' . esc_html__( 'Chef Selection', 'ravis-booking' ) . '</span>
										</label>
	                                </div>
                                    <div class="input-box title-box"><input type="text" name="" id="' . esc_attr( $field['id'] ) . '" data-name="title" value="" placeholder="' . esc_html__( 'Title', 'ravis-booking' ) . '" /></div>
                                    <div class="input-box price-box"><input type="text" name="" id="' . esc_attr( $field['id'] ) . '" data-name="price" value="" placeholder="' . esc_html__( 'Price (number only)', 'ravis-booking' ) . '" /></div>
                                </div>
                                <a class="repeatable-remove delete" href="#"><i class="dashicons dashicons-no"></i></a>
	                        </li>
                        </ul>
                        <span class="description">' . esc_html( $field['desc'] ) . '</span>';
					break;
					
					// Event Guest List
					case 'event_guest_list':
						$table_name     = $wpdb->prefix . 'ravis_event_booking';
						$event_bookings = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $table_name WHERE event_id = %d", $post->ID ) );
						echo '
							<table id="event-booking-list" class="wp-list-table widefat striped posts">
								<tr>
									<th class="num">#</th>
									<th class="num">' . esc_html__( 'Guest Name', 'ravis-booking' ) . '</th>
									<th class="num">' . esc_html__( 'Guest Phone', 'ravis-booking' ) . '</th>
									<th class="num">' . esc_html__( 'Guest Count', 'ravis-booking' ) . '</th>
									<th class="num">' . esc_html__( 'Email', 'ravis-booking' ) . '</th>
									<th class="num">' . esc_html__( 'Confirm', 'ravis-booking' ) . '</th>
									<th class="num">' . esc_html__( 'Delete', 'ravis-booking' ) . '</th>
								</tr>';
						$event_booking_i = 1;
						$total_guest     = 0;
						foreach ( $event_bookings as $event_booking_item )
						{
							echo '
							<tr class="num" data-event-booking-id="' . esc_attr( $event_booking_item->id ) . '">
								<td>' . esc_html( $event_booking_i ) . '</td>
								<td>' . esc_html( $event_booking_item->guest_name ) . '</td>
								<td>' . esc_html( $event_booking_item->phone ) . '</td>
								<td>' . esc_html( $event_booking_item->guest ) . '</td>
								<td>' . esc_html( $event_booking_item->email ) . '</td>
								<td><div data-nonce="' . wp_create_nonce( 'event_metabox_list' ) . '" class="confirm-item ' . ( $event_booking_item->status == 1 ? esc_attr( 'confirmed' ) : '' ) . '" title="' . ( $event_booking_item->status == 1 ? esc_html__( 'Confirmed', 'ravis-booking' ) : esc_html__( 'Pending', 'ravis-booking' ) ) . '"></div></td>
								<td><div data-nonce="' . wp_create_nonce( 'event_metabox_list' ) . '" class="delete-item"><i class="dashicons dashicons-no"></i></div></td>
							</tr>';
							$total_guest += $event_booking_item->guest;
							$event_booking_i ++;
						}
						echo '
								<tr>
									<th></th>
									<th></th>
									<th></th>
									<th class="num">' . esc_html( $total_guest ) . '</th>
									<th></th>
									<th></th>
									<th></th>
								</tr>
							</table>';
					break;
					
					// Service Price
					case 'service_price':
						echo '
							<div class="ravis-service-price-box">
								<input type="number" min="0" name="' . esc_attr( $field['id'] ) . '[price]" step="any" id="' . esc_attr( $field['id'] ) . '" value="' . esc_attr( ! empty( $meta['price'] ) ? $meta['price'] : '' ) . '" size="40" placeholder="' . esc_html__( 'Price ( number only)', 'ravis-booking' ) . '" />
								' . esc_html__( 'Per', 'ravis-booking' ) . '
								<select name="' . esc_attr( $field['id'] ) . '[guest]" id="' . esc_attr( $field['id'] ) . '" class="small">
									<option value="1" ' . ( ! empty( $meta['guest'] ) ? selected( $meta['guest'], 1, false ) : '' ) . '>' . esc_html__( 'Guest', 'ravis-booking' ) . '</option>
									<option value="2" ' . ( ! empty( $meta['guest'] ) ? selected( $meta['guest'], 2, false ) : '' ) . '>' . esc_html__( 'Booking', 'ravis-booking' ) . '</option>
									<option value="3" ' . ( ! empty( $meta['guest'] ) ? selected( $meta['guest'], 3, false ) : '' ) . '>' . esc_html__( 'Room', 'ravis-booking' ) . '</option>
								</select>
								<span class="service-price-type">
									' . esc_html__( 'Per', 'ravis-booking' ) . '
									<select name="' . esc_attr( $field['id'] ) . '[night]" id="' . esc_attr( $field['id'] ) . '" class="small">
										<option value="1" ' . ( ! empty( $meta['night'] ) ? selected( $meta['night'], 1, false ) : '' ) . '>' . esc_html__( 'Night', 'ravis-booking' ) . '</option>
										<option value="2" ' . ( ! empty( $meta['night'] ) ? selected( $meta['night'], 2, false ) : '' ) . '>' . esc_html__( 'Booking', 'ravis-booking' ) . '</option>
									</select>
								</span>
							</div>
							<span class="description">' . balancetags( $field['desc'] ) . '</span>';
					break;
					
					// Booking Overview Calendar
					case 'overview_calendar':
						wp_enqueue_script( 'moment-js', RAVIS_BOOKING_JS_PATH . 'moment.min.js', array ( 'jquery' ), RAVIS_BOOKING_VERSION, true );
						wp_enqueue_script( 'fullcalendar-js', RAVIS_BOOKING_JS_PATH . 'fullcalendar.min.js', array (
							'jquery',
							'moment-js'
						), RAVIS_BOOKING_VERSION, true );
						
						$web_current_locale = 'en';
						if ( get_locale() !== 'en_US' )
						{
							if ( file_exists( RAVIS_BOOKING_PATH . '\assets\js\locales.php' ) )
							{
								require( RAVIS_BOOKING_PATH . '\assets\js\locales.php' );
							}
							$web_current_locale = isset( $plugin_locales[ get_locale() ] ) ? $plugin_locales[ get_locale() ] : 'en';
							wp_enqueue_script( 'fullcalendar-locales-js', RAVIS_BOOKING_JS_PATH . 'locale/' . $web_current_locale . '.js', array ( 'jquery' ), RAVIS_BOOKING_VERSION, true );
							
						}
						$inline_locale_script = '
								jQuery(document).ready(function ($) {
									var roomCalendarContainer = jQuery(\'#room-calendar\');
									roomCalendarContainer.fullCalendar({
										locale:        \'' . esc_js( $web_current_locale ) . '\',
										eventMouseover: function (event, jsEvent, view) {
											var eventURL   = event.url,
												eventTitle = event.title;
								
											jQuery(\'.fc-event\').each(function (index, el) {
												var eventHref = jQuery(this).attr(\'href\'),
													eventText = jQuery(this).find(\'.fc-title\').text();
								
												if (eventHref == eventURL && eventText == eventTitle) {
													jQuery(this).addClass(\'hover-event\');
												}
											});
										},
										viewRender:     function (currentView) {
											var minDate = moment();
											if (minDate >= currentView.start && minDate <= currentView.end) {
												jQuery(".fc-prev-button").prop("disabled", true);
												jQuery(".fc-prev-button").addClass("fc-state-disabled");
											}
											else {
												jQuery(".fc-prev-button").removeClass("fc-state-disabled");
												jQuery(".fc-prev-button").prop("disabled", false);
											}
										},
										eventMouseout:  function (event, jsEvent, view) {
											jQuery(\'.fc-event\').removeClass(\'hover-event\');
										},
										eventSources:   [
											{
												events: function (start, end, timezone, callback) {
													var startDate = (start._d.getFullYear()) + \'-\' + (start._d.getMonth() + 1) + \'-\' + (start._d.getDate()),
														endDate   = (end._d.getFullYear()) + \'-\' + (end._d.getMonth() + 1) + \'-\' + (end._d.getDate());
													jQuery.ajax({
														url:      ravis_booking.ajaxurl,
														dataType: \'json\',
														method:   \'post\',
														data:     {
															action: "ravis_booking_room_overview",
															start:  startDate,
															end:    endDate,
															roomID: roomCalendarContainer.data(\'room-id\')
														}
													}).done(function (dataBooking) {
														var events = [];
														jQuery(dataBooking).each(function () {
															events.push({
																title:     jQuery(this).attr(\'title\'),
																start:     jQuery(this).attr(\'start\'),
																end:       jQuery(this).attr(\'end\'),
																rendering: jQuery(this).attr(\'rendering\'),
																color:     jQuery(this).attr(\'color\')
															});
														});
														callback(events);
													});
												}
											}
										]
									});
								});

							';
						
						if ( get_locale() !== 'en_US' )
						{
							wp_add_inline_script( 'fullcalendar-locales-js', $inline_locale_script );
						}
						else
						{
							wp_add_inline_script( 'fullcalendar-js', $inline_locale_script );
						}
						
						echo '
							<div id="room-calendar" data-room-id="' . esc_attr( $post->ID ) . '"></div>
							<div class="room-calendar-day-status-guide">
								<div class="status-box">
									<div class="box not-available"></div>
									<div class="title">' . esc_html__( 'Not Available', 'ravis-booking' ) . '</div>
								</div>
								<div class="status-box">
									<div class="box available"></div>
									<div class="title">' . esc_html__( 'Available', 'ravis-booking' ) . '</div>
								</div>
								<div class="status-box">
									<div class="box today">1</div>
									<div class="title">' . esc_html__( 'Today', 'ravis-booking' ) . '</div>
								</div>
							</div>
';
					break;
					
					
				} //end switch
				echo '</td></tr>';
			} // end foreach
			echo '</table>'; // end table
		}
		
		// Save the Data
		function save_meta_box( $post_id )
		{
			$security_code = '';
			
			if ( isset( $_POST[ $this->meta_box_post_type . '_meta_box_nonce' ] ) && $_POST[ $this->meta_box_post_type . '_meta_box_nonce' ] != '' )
			{
				$security_code = sanitize_text_field( $_POST[ $this->meta_box_post_type . '_meta_box_nonce' ] );
			}
			
			// verify nonce
			if ( ! wp_verify_nonce( $security_code, basename( __FILE__ ) ) )
			{
				return $post_id;
			}
			// check autosave
			if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )
			{
				return $post_id;
			}
			if ( ! current_user_can( 'edit_post', $post_id ) )
			{
				return $post_id;
			}
			
			// loop through fields and save the data
			foreach ( $this->meta_box_fields as $field )
			{
				$old = get_post_meta( $post_id, $field['id'], true );
				$new = $_POST[ $field['id'] ];
				if ( $new && $new != $old )
				{
					update_post_meta( $post_id, $field['id'], $new );
				}
				elseif ( '' == $new && $old )
				{
					delete_post_meta( $post_id, $field['id'], $old );
				}
			} // end foreach
		}
	}