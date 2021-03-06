<?php
/**
 * Template name: Events
 */

// city query param
$param_city = get_query_var('city');

// city query
$query_city = $param_city ? array(
	'key' => 'venue_city',
	'value' => $param_city,
	'compare' => '='
  ) : array();


if ($param_city == 'Online') {
	$query_city = array(
		'key' => 'venue_address',
		'value' => $param_city,
		'compare' => '='
	);
}

// page query param
$paged = (get_query_var('paged')) ? get_query_var('paged') : 1;

// Events Query
$args = array(
	'posts_per_page' => 10,
	'paged' => $paged,
	'post_type' => 'meetup_events',
	'orderby' => 'meta_value',
	'meta_key' => 'event_start_date',
	'order' => 'ASC',
	's' => '-cancelled -afgelast',
	'meta_query' => array(
		array(
			'key' => 'event_start_date', // Check the start date field
			'value' => date("Y-m-d"), // Set today's date (note the similar format)
			'compare' => '>=', // Return the ones greater than today's date
			'type' => 'DATE' // Let WordPress know we're working with date
		),
		$query_city
	)
);
$events = new WP_Query( $args );

$cities = event_cities();
get_header(); ?>

<div class="container my-5">
	<h1 class="page-title"><?php _e('EVENTS'); ?> <?php echo $param_city; ?></h1>

  <?php if ($cities) { ?>
    <form class="form-inline mt-4 flex-nowrap" method="get">
      <label class="my-1 mr-2" for="city">Location</label>
			<input type="hidden" name="paged" value="1" />
      <select name="city" class="custom-select my-1" id="city">
        <option value=""><?php _e('All') ?></option>
		<option disabled>──────</option>
		<?php if(array_key_exists('Online', $cities)) { ?>
			<option value='Online' <?php echo $param_city == 'Online' ? 'selected="selected"' : '' ?>>
				Online (<?php echo $cities['Online'] ?>)
			</option>
			<option disabled>──────</option>
		<?php } ?>
        <?php foreach($cities as $city => $count) { if ($city == 'Online') {continue;} ?>
          <option value="<?php echo $city ?>" <?php echo $param_city == $city ? 'selected="selected"' : '' ?>>
            <?php echo $city.' ('.$count.')' ?>
          </option>
        <?php } ?>
      </select>
      <button type="submit" class="btn btn-black ml-2">
        <?php _e('Apply') ?>
      </button>
    </form>
  <?php } ?>

	<div class="mt-4">
		<?php if ( $events->have_posts() ) : ?>
			<div class="row">
				<?php while ( $events->have_posts() ) : $events->the_post(); ?>
					<?php
					$event_date = get_post_meta( get_the_ID(), 'event_start_date', true );
					if( $event_date != '' ){
						$event_date = strtotime( $event_date );
					}
					$event_address = get_post_meta( get_the_ID(), 'venue_city', true );
					$venue_address = get_post_meta( get_the_ID(), 'venue_address', true );
					if( $event_address != '' && $venue_address != '' ){
						$event_address .= ' - '.$venue_address;
					}elseif( $venue_address != '' ){
						$event_address = $venue_address;
					}
					$image_url =  array();
					if ( '' !== get_the_post_thumbnail() ){
						$image_url =  wp_get_attachment_image_src( get_post_thumbnail_id(  get_the_ID() ), 'full' );
					}else{
						$image_date = date_i18n('F+d', $event_date );
						$image_url[] =  "http://placehold.it/420x150?text=".$image_date;
					}
					?>
					<div class="col-md-6 my-3">
						<a href="<?php echo esc_url( get_permalink() ) ?>">
							<div class="<?php echo $css_class; ?> archive-event">
								<div class="ime_event" >
									<div class="img_placeholder" style=" background: url('<?php echo $image_url[0]; ?>') no-repeat left top;"></div>
									<div class="event_details">
										<div class="event_date">
											<span class="month"><?php echo date_i18n('M', $event_date) ; ?></span>
											<span class="date"> <?php echo date_i18n('d', $event_date) ; ?> </span>
										</div>
										<div class="event_desc">
											<a href="<?php echo esc_url( get_permalink() ) ?>" rel="bookmark">
											<?php the_title( '<div class="event_title">','</div>' ); ?>
											</a>
											<?php if( $event_address != '' ){ ?>
												<div class="event_address"><i class="fa fa-map-marker"></i> <?php echo $event_address; ?></div>
											<?php }	?>
										</div>
										<div style="clear: both"></div>
									</div>
								</div>
							</div>
						</a>
					</div>
				<?php endwhile; ?>
			</div>
			<nav class="pages my-5">
				<?php
				// Pagination
				$big = 999999999;
				$pagination = paginate_links(array(
					'base' => str_replace($big, '%#%', esc_url(get_pagenum_link($big))),
					'current' => max(1, $paged),
					'total' => $events->max_num_pages,
					'type' => 'array',
					'prev_text' => '&laquo; Previous',
					'next_text' => 'Next &raquo;',
				));
				?>
				<?php if ( ! empty( $pagination ) ) : ?>
					<ul class="pagination justify-content-center">
						<?php foreach ( $pagination as $key => $page_link ) : ?>
							<li class="page-item <?php if ( strpos( $page_link, 'current' ) !== false ) { echo ' active'; } ?>">
								<?php echo $page_link ?>
							</li>
						<?php endforeach ?>
					</ul>
				<?php endif ?>
			</nav>
			<?php _e('Check <a href="https://www.facebook.com/ExtinctionRebellionNL/events/" target="_blank">Facebook</a> or <a href="https://www.meetup.com/Extinction-Rebellion-NL/events/" target="_blank">Meetup</a> for latest events') ?>.
		<?php else :
			_e('Looks like there are no events, try clearing the filter and make sure to check <a href="https://www.facebook.com/ExtinctionRebellionNL/events/" target="_blank">Facebook</a> or <a href="https://www.meetup.com/Extinction-Rebellion-NL/events/" target="_blank">Meetup</a>');
		endif; ?>
	</div>
</div>

<?php get_footer(); ?>

