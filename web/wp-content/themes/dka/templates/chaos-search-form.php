<?php
$types = WPChaosSearch::get_search_var(WPDKASearch::QUERY_KEY_TYPE);
$organizations = WPChaosSearch::get_search_var(WPDKASearch::QUERY_KEY_ORGANIZATION);

global $facets;
$facets = array(
  WPDKASearch::QUERY_KEY_TYPE => WPChaosSearch::generate_facet("FormatTypeName", WPDKASearch::QUERY_KEY_TYPE),
  WPDKASearch::QUERY_KEY_ORGANIZATION => WPChaosSearch::generate_facet("DKA-Organization", WPDKASearch::QUERY_KEY_ORGANIZATION),
);

$advanced_search_expanded = ((/*!empty($types) ||*/ !empty($organizations)) ? " in" : "");
$dates = WPChaosSearch::get_search_var(WPChaosSearch::QUERY_KEY_DATE_RANGE); ?>
<form method="GET" action="<?php echo $page; ?>">
  <div class="row">
    <div class="form-group col-xs-12">
      <div class="input-group">
        <input class="form-control search-bar" maxlength="100" id="appendedInputButton" type="text" name="<?php echo WPChaosSearch::QUERY_KEY_FREETEXT; ?>" value="<?php echo WPChaosSearch::get_search_var(WPChaosSearch::QUERY_KEY_FREETEXT, 'esc_attr,trim'); ?>" placeholder="<?php echo $freetext_placeholder; ?>" />
        <span class="input-group-btn">
          <button type="submit" class="btn btn-primary btn-search" id="searchsubmit"><?php _ex('Search','verb','dka'); ?></button>
        </span>
      </div>
      <input type="hidden" name="<?php echo WPChaosSearch::QUERY_KEY_VIEW; ?>" value="<?php echo WPChaosSearch::get_search_var(WPChaosSearch::QUERY_KEY_VIEW, 'esc_attr'); ?>">
      <input type="hidden" name="<?php echo WPChaosSearch::QUERY_KEY_SORT; ?>" value="<?php echo WPChaosSearch::get_search_var(WPChaosSearch::QUERY_KEY_SORT, 'esc_attr'); ?>">
      <?php if (current_user_can(WPDKA::PUBLISH_STATE_CAPABILITY)): ?>
        <input type="hidden" name="<?php echo WPChaosSearch::QUERY_KEY_ONLY_PUBLISHED; ?>" value="<?php echo WPChaosSearch::get_search_var(WPChaosSearch::QUERY_KEY_ONLY_PUBLISHED, 'esc_attr'); ?>">
      <?php endif; ?>
    </div>
    <div class="hidden-md hidden-lg btn-advanced-search-container">
      <button class="btn btn-default btn-lg btn-advanced-search collapsed dropdown-toggle <?php echo $advanced_search_expanded; ?>" type="button" data-toggle="collapse" data-target="#advanced-search-container">
        <?php _e('Options','dka'); ?> <i class="icon-caret-down"></i>
      </button>
    </div>
    <div class="advanced_search_wrapper">
      <div id="advanced-search-container" class="visible-md visible-lg collapse<?php echo $advanced_search_expanded; ?>">
        <div class="form-group col-xs-6 col-md-12">
          <label class="input-label">Fra dato</label>
          <input class="form-control" type="date" name="<?php echo WPDKASearch::QUERY_KEY_DATE_RANGE; ?>['from']"
            placeholder="fx 24-12-1849"
            value="<?php echo isset($dates[0]) ? $dates[0] : ''; ?>">
        </div>
        <div class="form-group col-xs-6 col-md-12">
          <label class="input-label">Til dato</label>
          <input class="form-control" type="date" name="<?php echo WPDKASearch::QUERY_KEY_DATE_RANGE; ?>['to']"
            placeholder="fx 24-12-1945"
            value="<?php echo isset($dates[1]) ? $dates[1] : ''; ?>">
        </div>
        <div class="col-xs-12 form-group">
          <label class="input-label">Medietype</label>
          <div class="btn-group btn-group-media-type" data-toggle="buttons">
          <?php foreach(WPDKAObject::$format_types as $format_type => $args) : if($format_type == WPDKAObject::TYPE_IMAGE_AUDIO || $format_type == WPDKAObject::TYPE_UNKNOWN) continue;
            $active = in_array($format_type,(array)$types);
          ?>
            <label class="btn btn-default<?php echo ($active ? ' active' : ''); ?>" title="<?php echo $args['title']; ?>">
              <input type="checkbox" name="<?php echo WPDKASearch::QUERY_KEY_TYPE; ?>['<?php echo $format_type; ?>']" value="<?php echo $format_type; ?>" <?php checked($active); ?>>
              <i class="<?php echo $args['class']; ?>"></i>
            </label>
          <?php endforeach; ?>
          </div>
        </div>

      </div>
    </div>
  </div>
</form>
