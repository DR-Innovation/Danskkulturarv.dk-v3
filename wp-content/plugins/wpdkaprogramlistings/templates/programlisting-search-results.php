<?php get_header(); ?>

<?php $results = WPDKAProgramListings::get_programlisting_results(); ?>
<div class="programlisting-search-results row">
	<form method="GET" action="<?php echo get_permalink(get_option('wpdkaprogramlistings-page')); ?>">
		<div class="programlisting-count col-md-4 col-sm-12 col-xs-12">
			<?php 
				if (isset($results)) {
					printf(__('The search gave %s %s'), count($results), _n('program listing', 'program listings', count($results)));
				}
			?>
		</div>
		<div class="col-md-4 col-sm-6 col-xs-12">
			<div class="programlisting-year col-xs-3 col-sm-3">
				<select name="<?php echo WPDKAProgramListings::QUERY_KEY_YEAR; ?>">
					<option value="" disabled><?php _e('Year',WPDKAProgramListings::DOMAIN); ?></option>
			<?php for ($y = WPDKAProgramListings::START_YEAR; $y <= WPDKAProgramListings::END_YEAR; $y++): ?>
					<option value="<?php echo $y; ?>" <?php selected($year, $y); ?>><?php echo $y; ?></option>
			<?php endfor; ?>
				</select>
			</div>

			<div class="programlisting-month col-xs-6 col-sm-4">
				<select name="<?php echo WPDKAProgramListings::QUERY_KEY_MONTH; ?>">
					<option value="" disabled><?php _e('Month',WPDKAProgramListings::DOMAIN); ?></option>
			<?php for ($m = 1; $m <= 12; $m++): ?>
					<option value="<?php echo $m; ?>" <?php selected($month, $m); ?>><?php echo ucfirst(__(date('F', mktime(0,0,0,$m)))); ?></option>
			<?php endfor; ?>
				</select>
			</div>

			<div class="programlisting-day col-xs-3 col-sm-3">
				<select name="<?php echo WPDKAProgramListings::QUERY_KEY_DAY; ?>">
					<option value="" disabled><?php _e('Day',WPDKAProgramListings::DOMAIN); ?></option>
			<?php for ($d = 1; $d <= 31; $d++): ?>
					<option value="<?php echo $d; ?>" <?php selected($day, $d); ?>><?php echo $d; ?></option>
			<?php endfor; ?>
				</select>
			</div>

			<button type="submit" class="btn btn-default col-xs-12 col-sm-2"><?php _e('Search'); ?></button>
		</div>
	</form>
</div>
<div class="row programlisting-results">
	<?php if (isset($results)): ?>
		<?php foreach ($results as $r): ?>
			<div class="col-lg-6 col-md-12 col-sm-12 col-xs-12">
				<canvas id="<?php echo 'pdf-' . $r['_id']; ?>" class="pdf"></canvas>
				<a title="Download" href="<?php echo $r['_source']['url']; ?>"><?php _e('Download program listing', WPDKAProgramListings::DOMAIN); ?></a>
				<script>renderPDF("<?php echo $r['_source']['url']; ?>", "pdf-<?php echo $r['_id']; ?>");</script>
			</div>
		<?php endforeach; ?>
	<?php endif; ?>
</div>

<?php get_footer(); ?>