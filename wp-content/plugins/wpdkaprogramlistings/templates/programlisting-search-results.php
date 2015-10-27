<? get_header(); ?>
<?
    $results = WPDKAProgramListings::get_programlisting_results();
    $search_text = WPDKAProgramListings::get_programlisting_search_type() === WPDKAProgramListings::QUERY_KEY_FREETEXT;
?>


<!-- SEARCH BAR -->
<div class="container body-container">
<h1><? _e('TV and radio through time', WPDKAProgramListings::DOMAIN); ?></h1>
<!-- start search -->
<div class="search row"><? dynamic_sidebar( 'Top' ); ?></div>
<!-- end search -->

<div class="programlisting-search-results row">
    <div class="js-date-search-content">
      <form method="GET" action="<? echo get_permalink(get_option('wpdkaprogramlistings-page')); ?>">
        <div class="col-xs-12 col-md-3 col-sm-3 col-lg-2 col-lg-offset-2">
          <div class="programlisting-year">
            <select name="<? echo WPDKAProgramListings::QUERY_KEY_YEAR; ?>">
              <option value=""><? _e('Year', WPDKAProgramListings::DOMAIN); ?></option>
          <? for ($y = WPDKAProgramListings::START_YEAR; $y <= WPDKAProgramListings::END_YEAR; $y++): ?>
              <option value="<? echo $y; ?>" <? selected($year, $y); ?>><? echo $y; ?></option>
          <? endfor; ?>
            </select>
          </div>
        </div>
        <div class="col-xs-12 col-md-3 col-sm-3 col-lg-2">
          <div class="programlisting-month">
            <select name="<? echo WPDKAProgramListings::QUERY_KEY_MONTH; ?>">
              <option value=""><? _e('Month', WPDKAProgramListings::DOMAIN); ?></option>
          <? for ($m = 1; $m <= 12; $m++): ?>
              <option value="<? echo $m; ?>" <? selected($month, $m); ?>><? echo ucfirst(__(date('F', mktime(0,0,0,$m,1)))); ?></option>
          <? endfor; ?>
            </select>
          </div>
        </div>
        <div class="col-xs-12 col-md-3 col-sm-3 col-lg-2">
          <div class="programlisting-day">
            <select name="<? echo WPDKAProgramListings::QUERY_KEY_DAY; ?>">
              <option value=""><? _e('Day', WPDKAProgramListings::DOMAIN); ?></option>
          <? for ($d = 1; $d <= 31; $d++): ?>
              <option value="<? echo $d; ?>" <? selected($day, $d); ?>><? echo $d; ?></option>
          <? endfor; ?>
            </select>
          </div>
        </div>
        <div class="col-xs-12 col-md-3 col-sm-3 col-lg-2">
          <button type="submit" class="btn btn-primary btn-block"><? _e('Search on date', WPDKAProgramListings::DOMAIN); ?></button>
        </div>
      </form>
    </div>
    <noscript>
        <form method="GET" action="<? echo get_permalink(get_option('wpdkaprogramlistings-page')); ?>">
            <div class="col-xs-12 col-md-3 col-sm-3 col-lg-2">
                <div class="programlisting-year">
                    <select name="<? echo WPDKAProgramListings::QUERY_KEY_YEAR; ?>">
                        <option value=""><? _e('Year', WPDKAProgramListings::DOMAIN); ?></option>
                <? for ($y = WPDKAProgramListings::START_YEAR; $y <= WPDKAProgramListings::END_YEAR; $y++): ?>
                        <option value="<? echo $y; ?>" <? selected($year, $y); ?>><? echo $y; ?></option>
                <? endfor; ?>
                    </select>
                </div>
            </div>
            <div class="col-xs-12 col-md-3 col-sm-3 col-lg-2">
                <div class="programlisting-month">
                    <select name="<? echo WPDKAProgramListings::QUERY_KEY_MONTH; ?>">
                        <option value=""><? _e('Month', WPDKAProgramListings::DOMAIN); ?></option>
                <? for ($m = 1; $m <= 12; $m++): ?>
                        <option value="<? echo $m; ?>" <? selected($month, $m); ?>><? echo ucfirst(__(date('F', mktime(0,0,0,$m,1)))); ?></option>
                <? endfor; ?>
                    </select>
                </div>
            </div>
            <div class="col-xs-12 col-md-3 col-sm-3 col-lg-2">
                <div class="programlisting-day">
                    <select name="<? echo WPDKAProgramListings::QUERY_KEY_DAY; ?>">
                        <option value=""><? _e('Day', WPDKAProgramListings::DOMAIN); ?></option>
                <? for ($d = 1; $d <= 31; $d++): ?>
                        <option value="<? echo $d; ?>" <? selected($day, $d); ?>><? echo $d; ?></option>
                <? endfor; ?>
                    </select>
                </div>
            </div>
            <div class="col-xs-12 col-md-3 col-sm-3 col-lg-2">
                <button type="submit" class="btn btn-primary btn-block"><? _e('Search on date', WPDKAProgramListings::DOMAIN); ?></button>
            </div>
        </form>
        <form class="free-text-search>" method="GET" action="<? echo get_permalink(get_option('wpdkaprogramlistings-page')); ?>">
            <div class="col-xs-12 col-lg-6 col-sm-9 col-lg-offset-3 col-md-offset-0">
                <input type="text" class="programlistings-search-text" name="<? echo WPDKAProgramListings::QUERY_KEY_FREETEXT; ?>" class="form-control" placeholder="<? _e('Search in program listings', WPDKAProgramListings::DOMAIN); ?>" value="<? echo WPDKAProgramListings::get_programlisting_var(WPDKAProgramListings::QUERY_KEY_FREETEXT, 'esc_attr,trim'); ?>" />
            </div>
            <div class="col-xs-12 col-lg-2 col-sm-3">
                <button type="submit" class="btn btn-primary btn-search btn-block" id="searchsubmit"><? _e('Search on date', WPDKAProgramListings::DOMAIN); ?></button>
            </div>
        </form>
    </noscript>
</div>
<!-- END SEARCH BAR -->

<!-- Search results -->
<div class="row programlisting-results">
  <? if (isset($results) && !$search_text): ?>
<!-- LOOP through pdf previews -->
    <? foreach ($results as $r): ?>
      <div class="col-lg-6 col-md-12 col-sm-12 col-xs-12">

        <!--[if !IE]><!-->
        <? echo do_shortcode('[pdfjs-viewer url=' . $r['_source']['url'] . ' viewer_width=600px viewer_height=700px fullscreen=true download=true print=true openfile=false]'); ?>
        <!--<![endif]-->

        <!--[if !IE]><!--><noscript><!--<![endif]-->
        <a href="<? echo $r['_source']['url']; ?>" alt="Download PDF"><? _e('Download program listing', WPDKAProgramListings::DOMAIN); ?></a>
        <!--[if !IE]><!--></noscript><!--<![endif]-->

      </div>
    <? endforeach; ?>
<!-- END LOOP -->
    <? else: ?>
        <div class="col-xs-12">
            <? if (isset($results)): ?>
                <p class="results-count">
                    <? if (count($results) == 100): ?>
                      <?{_e('There are more results than the 100 we found. Try being more precise. Hover the "i" button for hints.', WPDKAProgramListings::DOMAIN);} ?>
                    <? else: ?>
                      <? printf(_n('%d result', '%d results', count($results), WPDKAProgramListings::DOMAIN), count($results)); ?>
                    <? endif; ?>
                </p>
                <? if (!empty($results)): ?>
                    <ul class="list-unstyled search-overview">
                        <li class="row">
                            <div class="col-xs-4 col-sm-2"><strong><? _e('Date', WPDKAProgramListings::DOMAIN); ?></strong></div>
                            <div class="col-xs-8 col-sm-3 right"><strong><? _e('Type'); ?></strong></div>
                        </li>
                      <? foreach ($results as $r): ?>
                        <li class="row">
                            <div class="col-xs-4 col-sm-2">
                                <form method="GET" action="<? echo get_permalink(get_option('wpdkaprogramlistings-page')); ?>">
                                    <?
                                        $date = date(WPDKAProgramListings::DATE_FORMAT, strtotime($r['_source']['date']));
                                        $date_explode = explode('-', $date);
                                    ?>
                                    <input type="hidden" value="<? echo $date_explode[2]; ?>" name="<? echo WPDKAProgramListings::QUERY_KEY_YEAR; ?>" />
                                    <input type="hidden" value="<? echo $date_explode[1]; ?>" name="<? echo WPDKAProgramListings::QUERY_KEY_MONTH; ?>" />
                                    <input type="hidden" value="<? echo $date_explode[0]; ?>" name="<? echo WPDKAProgramListings::QUERY_KEY_DAY; ?>" />
                                    <button type="submit" class="btn btn-link"><? echo $date; ?></button>
                                </form>
                            </div>
                            <div class="col-xs-8 col-sm-3 right type"><? echo $r['_source']['type'] == 'Program' ? 'Programoversigt' : 'Rettelse til programoversigt'; ?></div>
                            <div class="col-xs-12 col-sm-7 col-lg-7 right">
                              <? echo do_shortcode('[no-pdfjs-viewer url=' . $r['_source']['url'] . ' viewer_width=600px viewer_height=700px fullscreen=true download=true print=true openfile=false]'); ?>
                            </div>
                        </li>
                      <? endforeach; ?>
                    </ul>
                <? endif; ?>
            <? endif; ?>
        </div>
  <? endif; ?>
</div>

<? get_footer(); ?>
