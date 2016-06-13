<?php
$results = WPDKAProgramListings::get_programlisting_results();
$results_total = WPDKAProgramListings::get_programlisting_total();
$search_text = WPDKAProgramListings::get_programlisting_search_type() === WPDKAProgramListings::QUERY_KEY_FREETEXT;
$larm_notice = 'Bemærk at det er sendeplaner fra 1925-1983, der er tilgængelige for søgning. Ønsker du en mere avanceret søgning kan du bruge <a href="http://www.larm.fm">larm.fm</a>';

add_filter('wpchaos-head-meta',function($metadatas) {
  $socialDescription = 'På danskkulturarv.dk/programoversigt/ kan du finde alle DRs programoversigter fra perioden 1925-1984. Tag et kig på hvad der blev sendt i radioen under krigen eller hvad kom i fjernsynet på din fødselsdag.';
  $metadatas['description']['content'] = $socialDescription;
  $metadatas['og:description']['content'] = $socialDescription;
  $metadatas['twitter:description']['content'] = $socialDescription;

  $socialTitle = 'DR sendeplan';
  $metadatas['og:title']['content'] = $socialTitle;
  $metadatas['twitter:title']['content'] = $socialTitle;

  $pageThumbnail = get_template_directory_uri() . '/img/programoversigt.jpg';
  $metadatas['og:image']['content'] = $pageThumbnail;
  $metadatas['twitter:image']['content'] = $pageThumbnail;

  $metadatas['twitter:image']['content'] = $pageThumbnail;
  return $metadatas;
});

get_header();
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
        <h1>Sendeplan</h1>
        <?php
          $pdf_slug = htmlspecialchars($_GET["pdf"]);
          echo do_shortcode('[pdfjs-viewer download=true social=true url=http://files.danskkulturarv.dk/'.$pdf_slug.'.pdf]');
        ?>
        <br />
        <br />
        <?php
          $this_page = "http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
          dka_social_share(array('link' => $this_page)); ?>
        <br />
        <?php echo $larm_notice; ?>
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
              <div class="row">
                <div class="col-xs-12 col-md-5 col-md-push-7">
                  <p class="programlisting-instructions">
                    <small><?php echo $larm_notice; ?></small>
                  </p>
                </div>
                <div class="col-xs-12 col-md-7 col-md-pull-5">
                  <p class="results-count">
                      <?php printf(__('Showing %d', WPDKAProgramListings::DOMAIN), count($results)); ?>
                      <?php printf(_n('out of %d results.', 'out of %d results.', $results_total, WPDKAProgramListings::DOMAIN), $results_total); ?>
                      <?php if ($results_total >= 500): ?>
                        <?php printf(_e('Try being more precise. Press the "i" button for hints.', WPDKAProgramListings::DOMAIN), $results_total); ?>
                      <?php endif; ?>
                  </p>
                </div>
              </div>
                <?php if (!empty($results)): ?>
                    <ul class="list-unstyled search-overview">
                        <li class="row">
                            <div class="col-xs-12 col-sm-3"><strong><?php _e('Date', WPDKAProgramListings::DOMAIN); ?></strong>
                            </div>
                            <div class="hidden-xs col-sm-4"><strong><?php _e('Type'); ?></strong></div>
                        </li>
                      <?php foreach ($results as $r): ?>
                        <li class="row">
                            <div class="col-xs-4 col-sm-3 type">
                                <?php
                                    $date = date(WPDKAProgramListings::DATE_FORMAT, strtotime($r['_source']['date']));
                                    $date_explode = explode('-', $date);
                                    echo str_replace('1056', '1956', $date);
                                ?>
                            </div>
                            <div class="hidden-xs col-sm-4 type"><?php echo $r['_source']['type'] == 'Program' ? 'Programoversigt' : 'Rettelse til programoversigt'; ?></div>
                            <div class="col-xs-8 col-sm-5 programlisting-search-results__actions">
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
