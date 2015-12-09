<?php get_header(); ?>
<?php
    $results = WPDKAProgramListings::get_programlisting_results();
    $search_text = WPDKAProgramListings::get_programlisting_search_type() === WPDKAProgramListings::QUERY_KEY_FREETEXT;
?>

<div class="fluid-container body-container">
  <div class="dark-search">
    <div class="search row"><?php dynamic_sidebar('Top'); ?></div>
    <div class="programlisting-search-results search row">
      <p class="text-center"><?php _e('or choose a date:', WPDKAProgramListings::DOMAIN); ?></p>
        <div class="js-date-search-content">
          <form method="GET" action="<?php echo get_permalink(get_option('wpdkaprogramlistings-page')); ?>">
            <div class="col-xs-12 col-sm-8 col-sm-offset-2">
              <div class="btn-group btn-group-justified">
                <div class="dka-select btn-group">
                  <select name="<?php echo WPDKAProgramListings::QUERY_KEY_YEAR; ?>" class="btn btn-default">
                    <option value=""><?php _e('Year', WPDKAProgramListings::DOMAIN); ?></option>
                <?php for ($y = WPDKAProgramListings::START_YEAR; $y <= WPDKAProgramListings::END_YEAR; ++$y): ?>
                    <option value="<?php echo $y; ?>" <?php selected($year, $y); ?>><?php echo $y; ?></option>
                <?php endfor; ?>
                  </select>
                </div>
                <div class="dka-select btn-group">
                  <select name="<?php echo WPDKAProgramListings::QUERY_KEY_MONTH; ?>" class="btn btn-default">
                    <option value=""><?php _e('Month', WPDKAProgramListings::DOMAIN); ?></option>
                <?php for ($m = 1; $m <= 12; ++$m): ?>
                    <option value="<?php echo $m; ?>" <?php selected($month, $m); ?>><?php echo ucfirst(__(date('F', mktime(0, 0, 0, $m, 1)))); ?></option>
                <?php endfor; ?>
                  </select>
                </div>
                <div class="dka-select btn-group">
                  <select name="<?php echo WPDKAProgramListings::QUERY_KEY_DAY; ?>" class="btn btn-default">
                    <option value=""><?php _e('Day', WPDKAProgramListings::DOMAIN); ?></option>
                <?php for ($d = 1; $d <= 31; ++$d): ?>
                    <option value="<?php echo $d; ?>" <?php selected($day, $d); ?>><?php echo $d; ?></option>
                <?php endfor; ?>
                  </select>
                </div>
                <div class="btn-group">
                  <button type="submit" class="btn btn-primary btn-block"><?php _e('Search on date', WPDKAProgramListings::DOMAIN); ?></button>
                </div>
              </div>
            </div>
          </form>
        </div>
    </div>
    <!-- END SEARCH BAR -->
  </div>
</div>


<!-- Search results -->
<div class="container">
  <div class="row programlisting-results">
    <?php if (!isset($results) && !$search_text): ?>
      <p class="text-left">
        <?php _e('Search for a word or select a date to find a program schedule.', WPDKAProgramListings::DOMAIN); ?>
      </p>
    <?php elseif (isset($results) && !$search_text): ?>
  <!-- LOOP through pdf previews -->
      <?php foreach ($results as $r): ?>
        <div class="col-md-6 text-center">

          <!--[if !IE]><!-->
          <?php echo do_shortcode('[pdfjs-viewer url='.$r['_source']['url'].' viewer_width=600px viewer_height=700px fullscreen=true download=true print=true openfile=false]'); ?>
          <!--<![endif]-->

          <!--[if !IE]><!--><noscript><!--<![endif]-->
          <a href="<?php echo $r['_source']['url']; ?>" alt="Download PDF"><?php _e('Download program listing', WPDKAProgramListings::DOMAIN); ?></a>
          <!--[if !IE]><!--></noscript><!--<![endif]-->

        </div>
      <?php endforeach; ?>
  <!-- END LOOP -->
      <?php else: ?>
          <div class="col-xs-12 col-md-10 col-md-offset-1 col-lg-8 col-lg-offset-2">
              <?php if (isset($results)): ?>
                  <div class="results-count">
                      <?php if (count($results) == 500): ?>
                        <?php {_e('There are more results than the 500 we show here. Try being more precise. Press the "i" button for hints.', WPDKAProgramListings::DOMAIN);} ?>
                      <?php else: ?>
                        <?php printf(_n('%d result', '%d results', count($results), WPDKAProgramListings::DOMAIN), count($results)); ?>
                      <?php endif; ?>
                  </div>
                  <div class="help-text">
                    <?php {_e('Klik på søgeresultatet for at se preview af alle sider fra den dato', WPDKAProgramListings::DOMAIN);} ?>
                  </div>
                  <?php if (!empty($results)): ?>
                      <ul class="list-unstyled search-overview">
                        <?php foreach ($results as $r):
                          $date = date(WPDKAProgramListings::DATE_FORMAT, strtotime($r['_source']['date']));
                          $date_explode = explode('-', $date);
                          $year = $date_explode[2];
                          $month = $date_explode[1];
                          $day = $date_explode[0];
                          $url = get_permalink(get_option('wpdkaprogramlistings-page')) . $year . '/' . $month . '/' . $day . '/';
                          $army_date = $year . '-' . $month . '-' . $day;
                        ?>
                          <li class="row fake-link" data-href="<?php echo $url ?>">
                            <div class="col-xs-4 col-sm-2 date">
                              <b><?php echo $army_date ?></b>
                            </div>
                            <div class="col-xs-8 col-sm-4 right type">
                              <?php echo $r['_source']['type'] == 'Program' ? 'Programoversigt' : 'Rettelse til programoversigt'; ?>
                            </div>
                            <div class="col-xs-12 col-sm-6 col-lg-6 right">
                              <?php echo do_shortcode('[no-pdfjs-viewer url='.$r['_source']['url'].']'); ?>
                            </div>
                          </li>
                        <?php endforeach; ?>
                      </ul>
                  <?php endif; ?>
              <?php endif; ?>
          </div>
    <?php endif; ?>
  </div>

</div>

<?php get_footer(); ?>
