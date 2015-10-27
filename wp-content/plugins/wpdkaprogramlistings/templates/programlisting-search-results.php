<?php get_header(); ?>
<?php
    $results = WPDKAProgramListings::get_programlisting_results();
    $search_text = WPDKAProgramListings::get_programlisting_search_type() === WPDKAProgramListings::QUERY_KEY_FREETEXT;
?>


<!-- SEARCH BAR -->
<div class="container body-container">
<h1><?php _e('TV and radio through time', WPDKAProgramListings::DOMAIN); ?></h1>
<!-- start search -->
<div class="search row"><?php dynamic_sidebar( 'Top' ); ?></div>
<!-- end search -->

<div class="programlisting-search-results row">
    <div class="js-date-search-content">
      <form method="GET" action="<?php echo get_permalink(get_option('wpdkaprogramlistings-page')); ?>">
        <div class="col-xs-12 col-md-3 col-sm-3 col-lg-2 col-lg-offset-2">
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
    <noscript>
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
        <form class="free-text-search>" method="GET" action="<?php echo get_permalink(get_option('wpdkaprogramlistings-page')); ?>">
            <div class="col-xs-12 col-lg-6 col-sm-9 col-lg-offset-3 col-md-offset-0">
                <input type="text" class="programlistings-search-text" name="<?php echo WPDKAProgramListings::QUERY_KEY_FREETEXT; ?>" class="form-control" placeholder="<?php _e('Search in program listings', WPDKAProgramListings::DOMAIN); ?>" value="<?php echo WPDKAProgramListings::get_programlisting_var(WPDKAProgramListings::QUERY_KEY_FREETEXT, 'esc_attr,trim'); ?>" />
            </div>
            <div class="col-xs-12 col-lg-2 col-sm-3">
                <button type="submit" class="btn btn-primary btn-search btn-block" id="searchsubmit"><?php _e('Search on date', WPDKAProgramListings::DOMAIN); ?></button>
            </div>
        </form>
    </noscript>
</div>
<!-- END SEARCH BAR -->

<!-- Search results -->
<div class="row programlisting-results">
  <?php if (isset($results) && !$search_text): ?>
<!-- LOOP through pdf previews -->
    <?php foreach ($results as $r): ?>
      <div class="col-lg-6 col-md-12 col-sm-12 col-xs-12">

        <!--[if !IE]><!-->
        <?php echo do_shortcode('[pdfjs-viewer url=' . $r['_source']['url'] . ' viewer_width=600px viewer_height=700px fullscreen=true download=true print=true openfile=false]'); ?>
        <!--<![endif]-->

        <!--[if !IE]><!--><noscript><!--<![endif]-->
        <a href="<?php echo $r['_source']['url']; ?>" alt="Download PDF"><?php _e('Download program listing', WPDKAProgramListings::DOMAIN); ?></a>
        <!--[if !IE]><!--></noscript><!--<![endif]-->

      </div>
    <?php endforeach; ?>
<!-- END LOOP -->
    <?php else: ?>
        <div class="col-xs-12">
            <?php if (isset($results)): ?>
                <p class="results-count">
                    <?php if (count($results) == 100): ?>
                      <?php {_e('There are more results than the 100 we found. Try being more precise. Hover the "i" button for hints.', WPDKAProgramListings::DOMAIN);} ?>
                    <?php else: ?>
                      <?php printf(_n('%d result', '%d results', count($results), WPDKAProgramListings::DOMAIN), count($results)); ?>
                    <?php endif; ?>
                </p>
                <?php if (!empty($results)): ?>
                    <ul class="list-unstyled search-overview">
                        <li class="row">
                            <div class="col-xs-4 col-sm-2"><strong><?php _e('Date', WPDKAProgramListings::DOMAIN); ?></strong></div>
                            <div class="col-xs-8 col-sm-3 right"><strong><?php _e('Type'); ?></strong></div>
                        </li>
                      <?php foreach ($results as $r): ?>
                        <li class="row">
                            <div class="col-xs-4 col-sm-2">
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
                            <div class="col-xs-8 col-sm-3 right type"><?php echo $r['_source']['type'] == 'Program' ? 'Programoversigt' : 'Rettelse til programoversigt'; ?></div>
                            <div class="col-xs-12 col-sm-7 col-lg-7 right">
                              <?php echo do_shortcode('[no-pdfjs-viewer url=' . $r['_source']['url'] . ' viewer_width=600px viewer_height=700px fullscreen=true download=true print=true openfile=false]'); ?>
                            </div>
                        </li>
                      <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            <?php endif; ?>
        </div>
  <?php endif; ?>
</div>

<?php get_footer(); ?>
