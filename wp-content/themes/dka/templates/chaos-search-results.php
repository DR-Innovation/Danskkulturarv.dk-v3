<?php
/**
 * @package WP Chaos Search
 * @version 1.0
 */
?>
<?php get_header();

$current_view = (WPChaosSearch::get_search_var(WPChaosSearch::QUERY_KEY_VIEW) ? 'listview' : 'thumbnails');
$current_sort = isset(WPDKASearch::$sorts[WPChaosSearch::get_search_var(WPChaosSearch::QUERY_KEY_SORT)]) ? WPDKASearch::$sorts[WPChaosSearch::get_search_var(WPChaosSearch::QUERY_KEY_SORT)]['title'] : WPDKASearch::$sorts[null]['title'];
$only_published_objects = WPChaosSearch::get_search_var(WPChaosSearch::QUERY_KEY_ONLY_PUBLISHED) == 'publicerede';

$views = array(
  array(
    'title' => __('View as List','wpchaossearch'),
    'view' => 'listview',
    'class' => 'icon-th-list icon-large',
    'link' => 'liste'
    ),
  array(
    'title' => __('View as Gallery','wpchaossearch'),
    'view' => 'thumbnails',
    'class' => 'icon-th icon-large',
    'link' => null
    ),
  );
  ?>

<div class="fluid-container body-container">
  <div class="dark-search">
    <div class="search row"><?php dynamic_sidebar('Top'); ?></div>
  </div>
</div>

<div class="container search-container">
  <article class="search-results">
    <div class="row search-results-top">
      <div class="col-xs-12 search-count">
        <?php
          $total_count = WPChaosSearch::get_search_results()->MCM()->TotalCount();
          $search_string = WPChaosSearch::get_search_var(WPChaosSearch::QUERY_KEY_FREETEXT, 'esc_html');
          if ($search_string){
            echo $result_count = sprintf(__('The search gave %s results for "%s"','dka'),number_format_i18n($total_count),'<strong>'.$search_string.'</strong>');
          } else {
            echo $result_count = sprintf(__('%s results','dka'),number_format_i18n($total_count));
          }
          ?>
      </div>
      <div class="col-xs-12 search-result-listing">
        <div class="btn-group">
           <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown"><?php _e('Sort by:','dka'); ?> <?php echo $current_sort; ?>
           </button>
           <ul class="dropdown-menu">
          <?php foreach(WPDKASearch::$sorts as $sort) : ?>
            <li><a tabindex="-1" href="<?php echo WPChaosSearch::generate_pretty_search_url(array(WPChaosSearch::QUERY_KEY_SORT => $sort['link'], WPChaosSearch::QUERY_KEY_PAGE => null)); ?>" title="<?php echo $sort['title']; ?>"><?php echo $sort['title']; ?></a></li>
          <?php endforeach; ?>
           </ul>
        </div>&nbsp;
        <div class="btn-group">
          <?php foreach($views as $view) :
          echo '<a type="button" class="btn btn-default'.($view['view'] == $current_view ? ' active' : '').'" href="'.WPChaosSearch::generate_pretty_search_url(array(WPChaosSearch::QUERY_KEY_VIEW => $view['link'])).'" title="'.$view['title'].'"><i class="'.$view['class'].'"></i></a>';
          endforeach; ?>
        </div>
        <?php if (current_user_can(WPDKA::PUBLISH_STATE_CAPABILITY)): ?>
          <div class="btn-group">
            <a type="button" class="btn btn-default <?php echo $only_published_objects ? ' active' : ''; ?>" title="<?php _e('Show only published objects', 'dka'); ?>" href="<?php echo WPChaosSearch::generate_pretty_search_url(array(WPChaosSearch::QUERY_KEY_ONLY_PUBLISHED => ($only_published_objects ? '' : 'publicerede'))); ?>"><i class="icon-eye-open"></i></a>
          </div>
        <?php endif; ?>
      </div>
    </div>
  <ul class="row <?php echo $current_view; ?>">

    <?php
    //Consider using a action/filter to print single result
    foreach(WPChaosSearch::get_search_results()->MCM()->Results() as $object) :
      $collection_obj = null;
      if(class_exists('WPDKACollections') && $object->ObjectTypeID == WPDKACollections::COLLECTIONS_TYPE_ID) :
        $collection_obj = new WPChaosObject($object,WPDKACollections::OBJECT_FILTER_PREFIX);
        //Safety. Should never happen
        if(!isset(WPDKACollections::$collection_relations[$object->GUID])) {
          continue;
        }
        $object = WPDKACollections::$collection_relations[$object->GUID];

      endif;
      WPChaosClient::set_object($object);
      $thumbnail = (WPChaosClient::get_object()->thumbnail ? ' style="background-image: url(\''.WPChaosClient::get_object()->thumbnail.'\')!important;"' : '');
      $url = WPChaosClient::get_object()->url;
      $caption = WPChaosClient::get_object()->caption;
      $title = WPChaosClient::get_object()->title;
      $organization = WPChaosClient::get_object()->organization;
      $views = number_format_i18n((double)WPChaosClient::get_object()->views);
      $class = '';
      $publish = current_user_can(WPDKA::PUBLISH_STATE_CAPABILITY) ? WPChaosClient::get_object()->isPublished ? '' : ' unpublished' : '';
      if($collection_obj) {
        $url .= '#'.$collection_obj->GUID;
        $caption = count($collection_obj->ObjectRelations) . ' ' . _n( 'material', 'materials', count($collection_obj->ObjectRelations), 'dka' );
        $title = $collection_obj->title;
        $organization = "Samling";
        $views = null;
        $class = ' collection-result';
      }
      ?>
      <li class="search-object col-xs-6 col-sm-4 col-lg-3<?php echo $class ?><?php echo $publish; ?>">
        <a class="thumbnail" href="<?php echo $url; ?>" id="<?php echo WPChaosClient::get_object()->GUID; ?>">

          <div class="thumb format-<?php echo WPChaosClient::get_object()->type; ?>"<?php echo $thumbnail; ?>>
            <?php  if($caption):?>
              <div class="caption"><?php echo $caption ?></div>
            <?php endif;?>
          <?php if(class_exists('WPDKACollections') && current_user_can(WPDKACollections::CAPABILITY) && !$collection_obj) : ?>
            <button type="button" class="add-to-collection btn"><span class="icon-plus"></span></button>
          <?php endif; ?>
          </div>
          <h2 class="title"><?php echo $title; ?></h2>
          <p class="organization"><?php echo $organization; ?></p>
          <?php if(WPChaosClient::get_object()->published && $collection_obj == null) : ?>
            <p class="date"><?php echo ltrim(get_chaos(published), "Året"); ?></p>
          <?php endif; ?>
          <div class="media-type-container">
            <i title="<?php echo WPChaosClient::get_object()->type_title; ?>" class="<?php echo WPChaosClient::get_object()->type_class; ?>"></i>
            <?php if(0 && $views) : ?>
            <i class="icon-eye-open"> <?php echo $views; ?></i>
            <?php endif; ?>
          </div>
        </a>
      </li>
<?php endforeach; WPChaosClient::reset_object(); ?>
</ul>

<div class="row">
  <div class="col-xs-12 text-center pagination-div">
    <ul class="pagination">
      <?php echo $pagination = WPChaosSearch::paginate('echo=0&before=&after=&count=5'); ?>
    </ul>
  </div>
</div>
</article>

<?php get_footer(); ?>
