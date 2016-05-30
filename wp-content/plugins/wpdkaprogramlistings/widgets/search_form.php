<?php
  $day   = WPDKAProgramListings::get_programlisting_var(WPDKAProgramListings::QUERY_KEY_DAY, 'esc_html');
  $month = WPDKAProgramListings::get_programlisting_var(WPDKAProgramListings::QUERY_KEY_MONTH, 'esc_html');
  $year  = WPDKAProgramListings::get_programlisting_var(WPDKAProgramListings::QUERY_KEY_YEAR, 'esc_html');
  $text_term = WPDKAProgramListings::get_programlisting_var(WPDKAProgramListings::QUERY_KEY_FREETEXT, 'esc_attr,trim');
?>

<div class="programlisting-search-results">
  <form method="GET" action="<?php echo get_permalink(get_option('wpdkaprogramlistings-page')); ?>">
    <div class="js-free-text-search-content schedule-free-text-search row">
      <div class="col-xs-8 col-xs-offset-2 search-field">
        <div class="input-group">
          <input type="text"
            name="<?=WPDKAProgramListings::QUERY_KEY_FREETEXT?>"
            class="form-control programlistings-search-text"
            placeholder="<?php _e('Search program schedule', WPDKAProgramListings::DOMAIN) ?>"
            value="<?=$text_term?>"
            data-original-title="" title="" />
          <div class="input-group-addon hover-info" data-html="true"
            data-container="body" data-toggle="popover"
            data-placement="bottom" data-trigger="hover"
            data-content="<?=WPDKAProgramListings::print_search_info_text() ?>">
            <i class="icon icon-info-sign"></i>
          </div>
        </div>
      </div>
    </div>
    <div class="js-date-search-content row">
      <div class="col-xs-4 col-sm-2 col-sm-offset-2">
        <div class="programlisting-year">
          <select name="<?= WPDKAProgramListings::QUERY_KEY_YEAR ?>">
            <option value=""><?php _e('Year', WPDKAProgramListings::DOMAIN); ?></option>
        <?php for ($y = WPDKAProgramListings::START_YEAR; $y <= WPDKAProgramListings::END_YEAR; ++$y): ?>
            <option value="<?php echo $y; ?>" <?php selected($year, $y); ?>><?php echo $y; ?></option>
        <?php endfor; ?>
          </select>
        </div>
      </div>
      <div class="col-xs-4 col-sm-2">
        <div class="programlisting-month">
          <select name="<?= WPDKAProgramListings::QUERY_KEY_MONTH ?>">
            <option value=""><?php _e('Month', WPDKAProgramListings::DOMAIN); ?></option>
        <?php for ($m = 1; $m <= 12; ++$m): ?>
            <option value="<?php echo $m; ?>" <?php selected($month, $m); ?>><?php echo ucfirst(__(date('F', mktime(0, 0, 0, $m, 1)))); ?></option>
        <?php endfor; ?>
          </select>
        </div>
      </div>
      <div class="col-xs-4 col-sm-2">
        <div class="programlisting-day">
          <select name="<?= WPDKAProgramListings::QUERY_KEY_DAY ?>">
            <option value=""><?php _e('Day', WPDKAProgramListings::DOMAIN); ?></option>
        <?php for ($d = 1; $d <= 31; ++$d): ?>
            <option value="<?php echo $d; ?>" <?php selected($day, $d); ?>><?php echo $d; ?></option>
        <?php endfor; ?>
          </select>
        </div>
      </div>
      <div class="col-xs-12 col-sm-2">
        <button type="submit" class="btn btn-primary btn-block"><?php _e('Search', WPDKAProgramListings::DOMAIN); ?></button>
      </div>
    </div>
  </form>
</div>
