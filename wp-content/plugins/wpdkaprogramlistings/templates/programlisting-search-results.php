<?php get_header(); ?>
<?php
    $results = WPDKAProgramListings::get_programlisting_results();
    $results_total = WPDKAProgramListings::get_programlisting_total();
    $search_text = WPDKAProgramListings::get_programlisting_search_type() === WPDKAProgramListings::QUERY_KEY_FREETEXT;
?>

<!-- START SEARCH BAR -->
<br />
<br />
<br />
<div class="fluid-container body-container search-container">
<?php dynamic_sidebar('Top'); ?>
</div>
<!-- END SEARCH BAR -->

<!-- Search results -->
<div class="container">
  <div class="row programlisting-results">
    <?php if (!isset($results) && !$search_text): ?>
      <div class="col-xs-12 col-md-10 col-md-offset-1 col-lg-8 col-lg-offset-2">
        <p class="text-left">
          <?php _e('Search for a word or select a date to find a program schedule.', WPDKAProgramListings::DOMAIN); ?>
          <br />
          Bemærk at det er sendeplaner fra 1925-1983, der er tilgængelige for søgning. Ønsker du en mere avanceret søgning kan du bruge <a href="http://www.larm.fm">larm.fm</a>
        </p>
      </div>
    <?php elseif (isset($results) && !$search_text): ?>
  <!-- LOOP through pdf previews -->
      <?php foreach ($results as $r): ?>
        <div class="col-md-6">

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
                  <p class="results-count">
                      <?php printf(__('Showing %d', WPDKAProgramListings::DOMAIN), count($results)); ?>
                      <?php printf(_n('out of %d results.', 'out of %d results.', $results_total, WPDKAProgramListings::DOMAIN), $results_total); ?>
                      <?php if ($results_total >= 500): ?>
                        <?php printf(_e('Try being more precise. Press the "i" button for hints.', WPDKAProgramListings::DOMAIN), $results_total); ?>
                      <?php endif; ?>
                  </p>
                  <p class="programlisting-instructions">
                    Bemærk at det er sendeplaner fra 1925-1983, der er tilgængelige for søgning. Ønsker du en mere avanceret søgning kan du bruge <a href="http://www.larm.fm">larm.fm</a>
                  </p>
                  <?php if (!empty($results)): ?>
                      <ul class="list-unstyled search-overview">
                          <li class="row">
                              <div class="col-xs-8 col-sm-4 col-lg-4"><strong><?php _e('Date', WPDKAProgramListings::DOMAIN); ?></strong>
                                <?php _e('- click to open preview', WPDKAProgramListings::DOMAIN); ?>
                              </div>
                              <div class="col-xs-4 col-sm-2 col-lg-2 right"><strong><?php _e('Type'); ?></strong></div>
                          </li>
                        <?php foreach ($results as $r): ?>
                          <li class="row">
                              <div class="col-xs-4 col-sm-2 col-lg-1">
                                  <form method="GET" action="<?php echo get_permalink(get_option('wpdkaprogramlistings-page')); ?>">
                                      <?php
                                          $date = date(WPDKAProgramListings::DATE_FORMAT, strtotime($r['_source']['date']));
                                          $date_explode = explode('-', $date);
                                      ?>
                                      <input type="hidden" value="<?php echo $date_explode[2]; ?>" name="<?php echo WPDKAProgramListings::QUERY_KEY_YEAR; ?>" />
                                      <input type="hidden" value="<?php echo $date_explode[1]; ?>" name="<?php echo WPDKAProgramListings::QUERY_KEY_MONTH; ?>" />
                                      <input type="hidden" value="<?php echo $date_explode[0]; ?>" name="<?php echo WPDKAProgramListings::QUERY_KEY_DAY; ?>" />
                                      <button type="submit" class="btn btn-link"><?php echo str_replace("1056","1956",$date); ?></button>
                                  </form>
                              </div>
                              <div class="col-xs-8 col-sm-4 col-lg-5 right type"><?php echo $r['_source']['type'] == 'Program' ? 'Programoversigt' : 'Rettelse til programoversigt'; ?></div>
                              <div class="col-xs-12 col-sm-6 col-lg-6 right">
                                <?php echo do_shortcode('[no-pdfjs-viewer url='.$r['_source']['url'].' viewer_width=600px viewer_height=700px fullscreen=true download=true print=true openfile=false]'); ?>
                              </div>
                          </li>
                        <?php endforeach; ?>
                      </ul>
                  <?php endif; ?>
              <?php endif; ?>
          </div>
    <?php endif; ?>
  </div>

  <div class="row">
    <div class="col-xs-12 text-center pagination-div">
      <ul class="pagination">
        <?php echo $pagination = WPDKAProgramListings::paginate('echo=0&before=&after=&count=5'); ?>
      </ul>
    </div>
  </div>
</div>

<?php get_footer(); ?>
