<?php get_header(); ?>
<?php
    $results = WPDKAProgramListings::get_programlisting_results();
    $results_total = WPDKAProgramListings::get_programlisting_total();
    $search_text = WPDKAProgramListings::get_programlisting_search_type() === WPDKAProgramListings::QUERY_KEY_FREETEXT;
    $larm_notice = 'Bemærk at det er sendeplaner fra 1925-1983, der er tilgængelige for søgning. Ønsker du en mere avanceret søgning kan du bruge <a href="http://www.larm.fm">larm.fm</a>'
?>


<div class="fluid-container body-container search-container">
  <div class="dark-search">
    <div class="search row"><?php dynamic_sidebar('Top'); ?></div>
  </div>
</div>


  <!-- Search results -->
<div class="container">
  <?php if (isset($_GET["pdf"])): ?>
    <div class="row single-pdf">
      <div class="col-md-10 col-md-offset-1 col-lg-8 col-lg-offset-2">
        <?php echo $larm_notice; ?>
        <div class="a4-wrap">
          <?php
            $pdf_slug = htmlspecialchars($_GET["pdf"]);
            echo '<iframe width="100%" height="100%" src="'.site_url().'/wp-content/plugins/wpdkaprogramlistings/pdfjs/web/viewer.php?file=http://files.danskkulturarv.dk/'.$pdf_slug.'.pdf"></iframe>';
          ?>
        </div>
      </div>
    </div>
  <?php else: ?>
    <div class="row programlisting-results">
      <?php if (!isset($results) && !$search_text): ?>
        <div class="col-xs-12 col-md-10 col-md-offset-1 col-lg-8 col-lg-offset-2">
          <p class="text-left">
            <?php _e('Search for a word or select a date to find a program schedule.', WPDKAProgramListings::DOMAIN); ?>
            <br />
            <?php echo $larm_notice; ?>
          </p>
        </div>
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
                  <?php echo $larm_notice; ?>
                </p>
                <?php if (!empty($results)): ?>
                    <ul class="list-unstyled search-overview">
                        <li class="row">
                            <div class="col-xs-8 col-sm-4 col-lg-4"><strong><?php _e('Date', WPDKAProgramListings::DOMAIN); ?></strong>
                            </div>
                            <div class="col-xs-4 col-sm-2 col-lg-2 right"><strong><?php _e('Type'); ?></strong></div>
                        </li>
                      <?php foreach ($results as $r): ?>
                        <li class="row">
                            <div class="col-xs-4 col-sm-2 col-lg-2 type">
                                <?php
                                    $date = date(WPDKAProgramListings::DATE_FORMAT, strtotime($r['_source']['date']));
                                    $date_explode = explode('-', $date);
                                    echo str_replace('1056', '1956', $date);
                                ?>
                            </div>
                            <div class="col-xs-8 col-sm-4 col-lg-5 right type"><?php echo $r['_source']['type'] == 'Program' ? 'Programoversigt' : 'Rettelse til programoversigt'; ?></div>
                            <div class="col-xs-12 col-sm-6 col-lg-5 right">
                              <?php echo do_shortcode('[pdfbuttons-viewer url='.$r['_source']['url'].']'); ?>
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
  <?php endif; ?>
</div>

<?php get_footer(); ?>
