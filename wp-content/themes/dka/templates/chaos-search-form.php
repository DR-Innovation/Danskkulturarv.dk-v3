<?php
$types = WPChaosSearch::get_search_var(WPDKASearch::QUERY_KEY_TYPE);
$organizations = WPChaosSearch::get_search_var(WPDKASearch::QUERY_KEY_ORGANIZATION);

global $facets;
$facets = array(
  WPDKASearch::QUERY_KEY_TYPE => WPChaosSearch::generate_facet("FormatTypeName", WPDKASearch::QUERY_KEY_TYPE),
  WPDKASearch::QUERY_KEY_ORGANIZATION => WPChaosSearch::generate_facet("DKA-Organization", WPDKASearch::QUERY_KEY_ORGANIZATION),
);
function get_facet_count($field, $values) {
  if(is_string($values)) {
    $values = array($values);
  }
  global $facets;
  $sum = 0;
  if(array_key_exists($field, $facets)) {
    foreach($values as $value) {
      if(array_key_exists($value, $facets[$field])) {
        $sum += intval($facets[$field][$value]);
      }
    }
  }
  return number_format_i18n($sum);
}
$advanced_search_expanded = ((/*!empty($types) ||*/ !empty($organizations)) ? " in" : "");
?>
<?php $dates = WPChaosSearch::get_search_var(WPChaosSearch::QUERY_KEY_DATE_RANGE); ?>
<form method="GET" action="<?php echo $page; ?>">
  <div class="row">
    <div class="form-group col-xs-9 col-sm-7 col-md-12">
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
      <button class="btn btn-default btn-lg btn-advanced-search collapsed dropdown-toggle btn-block <?php echo $advanced_search_expanded; ?>" type="button" data-toggle="collapse" data-target="#advanced-search-container">
        <span class="hidden-xs"><?php _e('Options','dka'); ?></span> <i class="icon-caret-down"></i>
      </button>
    </div>
    <div class="advanced_search_wrapper">
      <div id="advanced-search-container" class="visible-md visible-lg collapse<?php echo $advanced_search_expanded; ?>">
        <div class="form-group col-xs-6 padding-right-half">
          <label>Fra dato</label>
          <input class="form-control" type="date" name="<?php echo WPDKASearch::QUERY_KEY_DATE_RANGE; ?>['from']"
            placeholder="fx 24-12-1849"
            value="<?php echo isset($dates[0]) ? $dates[0] : ''; ?>">
        </div>
        <div class="form-group col-xs-6 padding-left-half">
          <label>Til dato</label>
          <input class="form-control" type="date" name="<?php echo WPDKASearch::QUERY_KEY_DATE_RANGE; ?>['to']"
            placeholder="fx 24-12-1945"
            value="<?php echo isset($dates[1]) ? $dates[1] : ''; ?>">
        </div>
        <div class="col-sm-3 col-md-12 form-group">
          <label>Medietype</label>
          <div class="btn-group btn-group-media-type" data-toggle="buttons">
      <?php foreach(WPDKAObject::$format_types as $format_type => $args) : if($format_type == WPDKAObject::TYPE_IMAGE_AUDIO || $format_type == WPDKAObject::TYPE_UNKNOWN) continue;

        $active = in_array($format_type,(array)$types);

      ?>
            <label class="btn btn-default<?php echo ($active ?
            ' active' : ''); ?>" title="<?php echo $args['title']; ?>">
              <input type="checkbox" name="<?php echo WPDKASearch::QUERY_KEY_TYPE; ?>['<?php echo $format_type; ?>']" value="<?php echo $format_type; ?>" <?php checked($active); ?>>
              <i class="<?php echo $args['class']; ?>"></i>
            </label>
      <?php endforeach; ?>
          </div>
        </div>
        <div class="col-xs-12 filter-container filter-organizations">
          <label>Institutioner</label>
          <div>
            <button class="btn btn-default btn-xs filter-btn filter-btn-all"><i class="icon-ok"></i><?php _e('All Organizations','dka'); ?></button>
      <?php
      $current_organization_id = 0;
      foreach(WPDKASearch::get_organizations_merged() as $id => $organization) :
      $count = get_facet_count(WPDKASearch::QUERY_KEY_ORGANIZATION, $organization['chaos_titles']);

      ?>
            <button for="<?php echo WPDKASearch::QUERY_KEY_ORGANIZATION .'-'. $organization['slug']; ?>" class="btn btn-default btn-xs filter-btn filter-btn-single">
              <input type="checkbox" class="chaos-filter" name="<?php echo WPDKASearch::QUERY_KEY_ORGANIZATION; ?>[]" value="<?php echo $organization['slug']; ?>" id="<?php echo WPDKASearch::QUERY_KEY_ORGANIZATION .'-'. $organization['slug']; ?>" <?php checked(in_array($organization['slug'],(array)$organizations)); ?>>
              <i class="icon-remove-sign"></i><?php echo $organization['title']; ?> (<?php echo $count ?>)
            </button>
      <?php endforeach; ?>
          </div>
        </div>
      </div>
    </div>
  </div>
</form>
