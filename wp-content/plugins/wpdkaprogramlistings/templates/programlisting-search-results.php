<?php get_header(); ?>
<?php 
    $results = WPDKAProgramListings::get_programlisting_results(); 
    $search_text = WPDKAProgramListings::get_programlisting_search_type() === WPDKAProgramListings::QUERY_KEY_FREETEXT;
?>
<div class="programlisting-search-results row">
    <div class="programlisting-count col-lg-3 col-md-9 col-sm-9 col-xs-10">
        <img src="<?php echo plugins_url( '../images/logo.png' , __FILE__ ); ?>" alt="TV og Radio" style="vertical-align: top; max-width: 100%; max-height: 38px;" />
    </div>
    <div class="col-xs-2 col-md-3 col-sm-3 col-lg-1 change-search pull-right">
        <button class="btn btn-default js-change-search pull-right" title="<?php _e('Full text search', WPDKAProgramListings::DOMAIN); ?>"><i class="icon icon-search"></i></button>
    </div>
    <div class="js-date-search-content<?php echo $search_text ? ' hidden' : ''; ?>">
    	<form method="GET" action="<?php echo get_permalink(get_option('wpdkaprogramlistings-page')); ?>">
    		<div class="col-xs-12 col-md-3 col-sm-3 col-lg-2">
    			<div class="programlisting-year">
    				<select name="<?php echo WPDKAProgramListings::QUERY_KEY_YEAR; ?>">
    					<option value=""><?php _e('Year', WPDKAProgramListings::DOMAIN); ?></option>
    			<?php for ($y = WPDKAProgramListings::START_YEAR; $y <= WPDKAProgramListings::END_YEAR; $y++): ?>
    					<option value="<?php echo $y; ?>" <?php selected($year, $y); ?>><?php echo $y; ?></option>
    			<?php endfor; ?>
    				</select>
    			</div>
    		</div>
    		<div class="col-xs-12 col-md-3 col-sm-3 col-lg-2">
    			<div class="programlisting-month">
    				<select name="<?php echo WPDKAProgramListings::QUERY_KEY_MONTH; ?>">
    					<option value=""><?php _e('Month', WPDKAProgramListings::DOMAIN); ?></option>
    			<?php for ($m = 1; $m <= 12; $m++): ?>
    					<option value="<?php echo $m; ?>" <?php selected($month, $m); ?>><?php echo ucfirst(__(date('F', mktime(0,0,0,$m,1)))); ?></option>
    			<?php endfor; ?>
    				</select>
    			</div>
    		</div>
    		<div class="col-xs-12 col-md-3 col-sm-3 col-lg-2">
    			<div class="programlisting-day">
    				<select name="<?php echo WPDKAProgramListings::QUERY_KEY_DAY; ?>">
    					<option value=""><?php _e('Day', WPDKAProgramListings::DOMAIN); ?></option>
    			<?php for ($d = 1; $d <= 31; $d++): ?>
    					<option value="<?php echo $d; ?>" <?php selected($day, $d); ?>><?php echo $d; ?></option>
    			<?php endfor; ?>
    				</select>
    			</div>
    		</div>
    		<div class="col-xs-12 col-md-3 col-sm-3 col-lg-2">
    			<button type="submit" class="btn btn-primary btn-block"><?php _e('Search on date', WPDKAProgramListings::DOMAIN); ?></button>
    		</div>
    	</form>
    </div>
    <div class="js-free-text-search-content <?php echo !$search_text ? ' hidden' : ''; ?>">
        <form method="GET" action="<?php echo get_permalink(get_option('wpdkaprogramlistings-page')); ?>">
            <div class="col-xs-12 col-lg-6 col-sm-9">
                <div class="input-group">
                    <input type="text" name="<?php echo WPDKAProgramListings::QUERY_KEY_FREETEXT; ?>" class="form-control programlistings-search-text" placeholder="<?php _e('Search in program listings', WPDKAProgramListings::DOMAIN); ?>" value="<?php echo WPDKAProgramListings::get_programlisting_var(WPDKAProgramListings::QUERY_KEY_FREETEXT, 'esc_attr,trim'); ?>" />
                    <div class="input-group-addon hover-info" data-html="true" data-container="body" data-toggle="popover" data-placement="bottom" data-trigger="hover" data-content="<?php WPDKAProgramListings::print_search_info_text(); ?>">
                        <i class="icon icon-info-sign"></i>
                    </div>
                </div>
            </div>
            <div class="col-xs-12 col-lg-2 col-sm-3" style="padding-bottom: 5px;">
                <button type="submit" class="btn btn-primary btn-search btn-block" id="searchsubmit"><?php _e('Search on date', WPDKAProgramListings::DOMAIN); ?></button>
            </div>
        </form>
    </div>
    <noscript>
        <form method="GET" class="<?php echo !$search_text ? 'hidden' : ''; ?>" action="<?php echo get_permalink(get_option('wpdkaprogramlistings-page')); ?>">
            <div class="col-xs-12 col-md-3 col-sm-3 col-lg-2">
                <div class="programlisting-year">
                    <select name="<?php echo WPDKAProgramListings::QUERY_KEY_YEAR; ?>">
                        <option value=""><?php _e('Year', WPDKAProgramListings::DOMAIN); ?></option>
                <?php for ($y = WPDKAProgramListings::START_YEAR; $y <= WPDKAProgramListings::END_YEAR; $y++): ?>
                        <option value="<?php echo $y; ?>" <?php selected($year, $y); ?>><?php echo $y; ?></option>
                <?php endfor; ?>
                    </select>
                </div>
            </div>
            <div class="col-xs-12 col-md-3 col-sm-3 col-lg-2">
                <div class="programlisting-month">
                    <select name="<?php echo WPDKAProgramListings::QUERY_KEY_MONTH; ?>">
                        <option value=""><?php _e('Month', WPDKAProgramListings::DOMAIN); ?></option>
                <?php for ($m = 1; $m <= 12; $m++): ?>
                        <option value="<?php echo $m; ?>" <?php selected($month, $m); ?>><?php echo ucfirst(__(date('F', mktime(0,0,0,$m,1)))); ?></option>
                <?php endfor; ?>
                    </select>
                </div>
            </div>
            <div class="col-xs-12 col-md-3 col-sm-3 col-lg-2">
                <div class="programlisting-day">
                    <select name="<?php echo WPDKAProgramListings::QUERY_KEY_DAY; ?>">
                        <option value=""><?php _e('Day', WPDKAProgramListings::DOMAIN); ?></option>
                <?php for ($d = 1; $d <= 31; $d++): ?>
                        <option value="<?php echo $d; ?>" <?php selected($day, $d); ?>><?php echo $d; ?></option>
                <?php endfor; ?>
                    </select>
                </div>
            </div>
            <div class="col-xs-12 col-md-3 col-sm-3 col-lg-2">
                <button type="submit" class="btn btn-primary btn-block"><?php _e('Search on date', WPDKAProgramListings::DOMAIN); ?></button>
            </div>
        </form>
        <form class="free-text-search<?php echo $search_text ? ' hidden' : ''; ?>" method="GET" action="<?php echo get_permalink(get_option('wpdkaprogramlistings-page')); ?>">
            <div class="col-xs-12 col-lg-6 col-sm-9 col-lg-offset-3 col-md-offset-0">
                <input type="text" class="programlistings-search-text" name="<?php echo WPDKAProgramListings::QUERY_KEY_FREETEXT; ?>" class="form-control" placeholder="<?php _e('Search in program listings', WPDKAProgramListings::DOMAIN); ?>" value="<?php echo WPDKAProgramListings::get_programlisting_var(WPDKAProgramListings::QUERY_KEY_FREETEXT, 'esc_attr,trim'); ?>" />
            </div>
            <div class="col-xs-12 col-lg-2 col-sm-3">
                <button type="submit" class="btn btn-primary btn-search btn-block" id="searchsubmit"><?php _e('Search on date', WPDKAProgramListings::DOMAIN); ?></button>
            </div>
        </form>
    </noscript>
</div>
<div class="row programlisting-results">
	<?php if (isset($results) && !$search_text): ?>
		<?php foreach ($results as $r): ?>
			<div class="col-lg-6 col-md-12 col-sm-12 col-xs-12">
				<?php echo do_shortcode('[pdfjs-viewer url=' . $r['_source']['url'] . ' viewer_width=600px viewer_height=700px fullscreen=true download=true print=true openfile=false]'); ?>
				<noscript><a href="<?php echo $r['_source']['url']; ?>" alt="Download PDF"><?php _e('Download program listing', WPDKAProgramListings::DOMAIN); ?></a></noscript>
			</div>
		<?php endforeach; ?>
    <?php else: ?>
        <div class="col-xs-12">
            <?php if (isset($results)): ?>
                <p class="results-count">
                    <?php printf(_n('%d result', '%d results', count($results), WPDKAProgramListings::DOMAIN), count($results)); ?>
                </p>
                <?php if (!empty($results)): ?>
                    <ul class="list-unstyled search-overview">
                        <li class="row">
                            <div class="col-xs-4"><strong><?php _e('Date', WPDKAProgramListings::DOMAIN); ?></strong></div>
                            <div class="col-xs-4"><strong><?php _e('Download to your computer', WPDKAProgramListings::DOMAIN); ?></strong></div>
                            <div class="col-xs-4 right"><strong><?php _e('Type'); ?></strong></div>
                        </li>
                    <?php foreach ($results as $r): ?>
                        <li class="row">
                            <div class="col-xs-4">
                                <form method="GET" action="<?php echo get_permalink(get_option('wpdkaprogramlistings-page')); ?>">
                                    <?php 
                                        $date = date(WPDKAProgramListings::DATE_FORMAT, strtotime($r['_source']['date'])); 
                                        $date_explode = explode('-', $date);
                                    ?>
                                    <input type="hidden" value="<?php echo $date_explode[2]; ?>" name="<?php echo WPDKAProgramListings::QUERY_KEY_YEAR; ?>" />
                                    <input type="hidden" value="<?php echo $date_explode[1]; ?>" name="<?php echo WPDKAProgramListings::QUERY_KEY_MONTH; ?>" />
                                    <input type="hidden" value="<?php echo $date_explode[0]; ?>" name="<?php echo WPDKAProgramListings::QUERY_KEY_DAY; ?>" />
                                    <button type="submit" class="btn btn-link"><?php echo $date; ?></button>
                                </form>
                            </div>
                            <div class="col-xs-4"><a href="<?php echo $r['_source']['url']; ?>" title="Download PDF" download="<?php echo $r['_source']['filename']; ?>"><?php echo $r['_source']['filename']; ?></a></div>
                            <div class="col-xs-4 right"><?php echo $r['_source']['type'] == 'Program' ? 'Programoversigt' : 'Rettelse til programoversigt'; ?></div>
                        </li>    
                    <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            <?php else: ?>
                <div class="full-text-search-div hidden-xs hidden-sm hidden-md">
                    <p><?php _e('Click to full text search', WPDKAProgramListings::DOMAIN); ?></p>
                    <canvas id="full-text-search-arrow" width="250" height="200"></canvas>
                </div>
            <?php endif; ?>
        </div>
	<?php endif; ?>
</div>

<?php get_footer(); ?>
