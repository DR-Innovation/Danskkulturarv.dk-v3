<?php
/**
 * @package WP Chaos Search
 * @version 1.0
 */
?>
<?php get_header();

$current_view = (WPChaosSearch::get_search_var(WPChaosSearch::QUERY_KEY_VIEW) ? 'listview' : 'thumbnails');
$current_sort = isset(WPDKASearch::$sorts[WPChaosSearch::get_search_var(WPChaosSearch::QUERY_KEY_SORT)]) ? WPDKASearch::$sorts[WPChaosSearch::get_search_var(WPChaosSearch::QUERY_KEY_SORT)]['title'] : WPDKASearch::$sorts[null]['title'];

$views = array(
	array(
		'title' => __('View as List','wpchaossearch'),
		'view' => 'listview',
		'class' => 'icon-th-list',
		'link' => 'liste'
		),
	array(
		'title' => __('View as Gallery','wpchaossearch'),
		'view' => 'thumbnails',
		'class' => 'icon-th',
		'link' => null
		),
	);

	?>
	<article class="container search-results">
		<div class="row search-results-top">
			<div class="col-4 col-sm-4">
				<p><?php echo $result_count = sprintf(__('<span class="hidden-sm">The search for %s gave&nbsp;</span><span>%s results</span>','wpchaossearch'),'<strong class="blue">'.WPChaosSearch::get_search_var(WPChaosSearch::QUERY_KEY_FREETEXT, 'esc_html').'</strong>', number_format_i18n(WPChaosSearch::get_search_results()->MCM()->TotalCount())); ?></p>
			</div>
			<div class="col-4 col-sm-2">	
				<div class="dropdown sortby-dropdown pull-right">
					<a class="sortby-link" id="dLabel" role="button" data-toggle="dropdown" data-target="#" href="#"><?php _e('Sort by:','dka'); ?> <strong class="blue"><?php echo $current_sort; ?></strong>&nbsp;<i class="icon-caret-down"></i></a>
					<ul class="dropdown-menu" role="menu" aria-labelledby="dropdownMenu">
						<?php foreach(WPDKASearch::$sorts as $sort) : ?>
						<li><a tabindex="-1" href="<?php echo WPChaosSearch::generate_pretty_search_url(array(WPChaosSearch::QUERY_KEY_SORT => $sort['link'], WPChaosSearch::QUERY_KEY_PAGE => null)); ?>" title="<?php echo $sort['title']; ?>"><?php echo $sort['title']; ?></a></li>
					<?php endforeach; ?>
				</ul>
			</div>
		</div>
		<div class="col-4 col-sm-2">
			<div class="search-result-listing btn-group">
				<?php foreach($views as $view) :
				echo '<a type="button" class="btn btn-default'.($view['view'] == $current_view ? ' active' : '').'" href="'.WPChaosSearch::generate_pretty_search_url(array(WPChaosSearch::QUERY_KEY_VIEW => $view['link'])).'" title="'.$view['title'].'"><i class="'.$view['class'].'"></i></a>';
				endforeach; ?>
			</div>
		</div>
		<div class="col-12 col-sm-4 pagination-div">
			<ul class="pagination pagination-large pull-right">
				<?php echo $pagination = WPChaosSearch::paginate('echo=0&before=&after=&count=5'); ?>
			</ul>
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
			$views = WPChaosClient::get_object()->views;
			$class = '';
			if($collection_obj) {
				$url .= '#'.$collection_obj->GUID;
				$caption = count($collection_obj->ObjectRelations) . ' ' . _n( 'material', 'materials', count($collection_obj->ObjectRelations), 'dka' );
				$title = $collection_obj->title;
				$organization = "Samling";
				$views = null;
				$class = ' collection-result';
			}
			?>
			<li class="search-object col-12 col-sm-6 col-lg-3<?php echo $class ?>">
				<a class="thumbnail" href="<?php echo $url; ?>" id="<?php echo WPChaosClient::get_object()->GUID; ?>">

					<div class="thumb format-<?php echo WPChaosClient::get_object()->type; ?>"<?php echo $thumbnail; ?>>
						<?php  if($caption):?>
						<div class="caption"><?php echo $caption ?></div>
					<?php endif;?>
				</div>
				<h2 class="title"><strong><?php echo $title; ?></strong></h2>
				<p class="organization"><strong class="strong orange organization"><?php echo $organization; ?></strong>
					<?php if(WPChaosClient::get_object()->published && $collection_obj == null) : ?></p>
					<p class="date"><i class="icon-calendar"></i> <?php echo WPChaosClient::get_object()->published; ?></p>
				<?php endif; ?>
				<hr>
				<div class="media-type-container">
					<i title="<?php echo WPChaosClient::get_object()->type_title; ?>" class="<?php echo WPChaosClient::get_object()->type_class; ?>"></i>
					<?php if($views) : ?>
					<i class="icon-eye-open"> <?php echo $views; ?></i>
					<?php endif; ?>
				</div>
				<?php if(class_exists('WPDKACollections') && current_user_can(WPDKACollections::CAPABILITY) && !$collection_obj) : ?>
				<button type="button" class="add-to-collection btn btn-primary"><span class="icon-plus"></span></button>
			<?php endif; ?>
		</a>
	</li>
<?php endforeach; WPChaosClient::reset_object(); ?>
</ul>

<div class="row search-results-top">
	<div class="col-sm-6 hidden-sm">
		<p><?php echo $result_count; ?></p>
	</div>
	<div class="col-12 col-sm-6">
		<ul class="pagination pagination-large pull-right">
			<?php echo $pagination; ?>
		</ul>
	</div>
</div>
</article>

<?php get_footer(); ?>
