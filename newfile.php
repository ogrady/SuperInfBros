
<?php

/**
  * Replacing $mob_data with real data from WordPress meta.
  * 
  * --bth 2015-03-24 
  */ 

	function getSearchData(){
		// Klasse
		$classes = sort(array_unique(getMetaValues('class', 'fahrzeuge')));
		sort($classes);
		$search_data['classes'] = $classes;
		
		// Hersteller
		$makes = array_unique(getMetaValues('make', 'fahrzeuge'));
		sort($makes);
		$search_data['makes'] = $makes;
		
		// Modell
		$models = array_unique(getMetaValues('model', 'fahrzeuge'));
		sort($models);
		$search_data['models'] = $models;
		
		// Kategorie
		$categories = array_unique(getMetaValues('category', 'fahrzeuge'));
		sort($categories);
		$search_data['categories'] = $categories;
		
		// Fahrzeugtyp und Nutzungstyp
		$categories = array_unique(getMetaValues('category', 'fahrzeuge'));
		$usage_types = array_unique(getMetaValues('usage_type', 'fahrzeuge'));
		$types = array_filter(array_merge($categories, $usage_types));
		sort($types);
		$search_data['types'] = $types;
		
		return $search_data;
	}
	
	/****
	 * 
	 * 
	 */
	function getMetaValues($key = '', $type = 'post', $status = 'publish') {
	global $wpdb;

	if (empty($key))
		return;

	$r = $wpdb->get_col(
			$wpdb->prepare(
					"
					SELECT pm.meta_value FROM {$wpdb->postmeta} pm
					LEFT JOIN {$wpdb->posts} p ON p.ID = pm.post_id
					WHERE pm.meta_key = '%s'
					AND p.post_status = '%s'
					AND p.post_type = '%s'
					", $key, $status, $type));

	return $r;
}

?>

<script type="text/javascript">
var ajaxurl = "<?php echo admin_url('admin-ajax.php'); ?>";

var classes={};
var makes={};
var models={};
var categories={};


<?php 

// Classes.
if(empty($instance['class_list'])) {
	$class_list = array_unique(getMetaValues('class', 'fahrzeuge'));
	sort($class_list);
}else {
	// foreach($instance['class_list'] as $key) {
	// 	$classKey = $key;
	// 	$class_list[$classKey]=$this->mob_data['classes'][$classKey];
	// }
}

// Makes.
if(empty($instance['make_list'])) {
	$make_list = array_unique(getMetaValues('class', 'fahrzeuge'));
	sort($make_list);
} else {
	// foreach($instance['brand_list'] as $key) {
	// 	list($classKey,$brandKey)=explode('::', $key);
	// 	$brand_list[$classKey][$brandKey]=$this->mob_data['brands'][$classKey][$brandKey];
	// }
}

// Categories.
if(empty($instance['category_list'])) {
	$category_list = array_unique(getMetaValues('category', 'fahrzeuge'));
	sort($category_list);
} else {
	// foreach($instance['category_list'] as $key) {
	// 	list($classKey,$categoryKey)=explode('::', $key);
	// 	$category_list[$classKey][$categoryKey]=$this->mob_data['categories'][$classKey][$categoryKey];
	// }
}

// Models.
if(empty($instance['model_list'])) {
	$model_list = array_unique(getMetaValues('model', 'fahrzeuge'));
	sort($model_list);
} else {
	// foreach($instance['model_list'] as $key) {
	// 	list($classKey,$brandKey,$modelKey)=explode('::', $key);
	// 	$model_list[$classKey][$brandKey][$modelKey]=$this->mob_data['models'][$classKey][$brandKey][$modelKey];
	// }
}

$options = get_option( 'MobileDE_option' );


// $search_data = getSearchData();

// if(!empty($search_data)) {
// 	$class_list = $search_data['classes'];
// 	$brand_list = $search_data['makes'];
// 	$category_list = $search_data['categories'];
// 	$model_list = $search_data['models'];
// } else {
// }

$options = get_option( 'MobileDE_option' );

// log_me($instance);

$showClass = $instance['class'] || count($instance['class_list']) > 1;
$showCategory = ($showClass || count($instance['class_list']) > 0 || !empty($options['class']))&& ($instance['category'] || count($instance['category_list']) > 1);
$showMake=($showClass || count($instance['class_list']) > 0 || !empty($options['class']))&& ($instance['make'] || count($instance['make_list']) > 1);
$showModel=($showClass || count($instance['class_list']) > 0 || !empty($options['class']))&& ($showMake || count($instance['make_list']) > 0 || !empty($options['make'])) 
		&& ($instance['model'] || count($instance['model_list']) > 1);
/*
$class_list=empty($instance['class_list'])?$this->mob_data['classes']:$instance['class_list'];
$category_list=empty($instance['category_list'])?$this->mob_data['categories']:$instance['category_list'];
$brand_list=empty($instance['brand_list'])?$this->mob_data['brands']:$instance['brand_list'];
$model_list=empty($instance['model_list'])?$this->mob_data['models']:$instance['model_list'];
*/
if(!$showClass) $_GET['mob_class'] = array_keys($class_list)[0];
if(!$showMake) $_GET['mob_make'] = array_keys($make_list)[0];



// Populate list items.
?>
class_list={};

<?php
if(!empty($class_list)){
	foreach($class_list as $i => $class){
		?>
			class_list['<?=$i?>']='<?=$class?>';
		<?php
	}
}
?>


category_list={};
<?php 
if(!empty($category_list)){
	foreach($category_list as $i => $category){
		?>
			category_list['<?=$i?>']='<?=$category?>';
		<?php
	}
}
?>

make_list={};
<?php 
	if(!empty($make_list)){
		foreach($make_list as $i => $make){
			?>
				make_list['<?=$i?>']='<?=$make?>';
			<?php
		}
	}
?>

model_list={};

<?php 
if(!empty($model_list)){
	foreach($model_list as $i => $model){
		?>
			model_list['<?=$i?>']='<?=$model?>';
		<?php
	}
}
?>

 jQuery(document).ready(function(){

 	var classesHtml='<option value="">Alle</option>';
 	for(value in class_list) {
 		name=class_list[value];

 		var selected='';
		if('<?=$_GET['mob_class']?>'==value) {
 			selected='selected="selected" ';
 		} 		classesHtml+='<option '+selected+'value="'+value+'">'+name+'</option>';
	}
 	jQuery('#class').html(classesHtml);

 	jQuery('body').on('change', '#class', function() {

 		// Populate categories and makes.
 		makesHtml='<option data-value="" value="">Alle</option>';
 		categoriesHtml='<option data-value="" value="">Alle</option>';

 		jQuery(this).find('option:selected').each(function() {	

 			makesInClass=make_list[jQuery(this).attr('value')];
 			for(i in makesInClass) {
 				value=jQuery(this).attr('value')+'::'+i;

 				var selected='';
 				if('<?=$_GET['mob_class']?>::<?=$_GET['mob_make']?>'==value) {
					selected='selected="selected" ';
				}
 				makesHtml+='<option data-value="'+value+'" '+selected+'value="'+i+'">'+makesInClass[i]+'</option>';
			}
			
	 		categoriesInClass=category_list[jQuery(this).attr('value')];
	 		for(i in categoriesInClass) {
	 			var value = jQuery(this).attr('value')+'::'+i;
	 			var selected='';
	 			if('<?=$_GET['mob_class']?>::<?=$_GET['mob_category']?>'==value) {
	 				selected='selected="selected" ';
				}
 			categoriesHtml+='<option data-value="'+value+'" '+selected+'value="'+i+'">'+categoriesInClass[i]+'</option>';
			}
		});

	 	jQuery('#make').html(makesHtml);
		jQuery('#category').html(categoriesHtml);
	 	jQuery('#make').trigger('change');
	 	jQuery('#category').trigger('change');

	 });

	 jQuery('body').on('change', '#make', function() {
	 	//populate models
	 	modelsHtml='';
			
	 	jQuery(this).find('option:selected').each(function() {	

	 		key=jQuery(this).data('value').split('::');
			if(key[1] !=null) {
	 			modelsInClass=model_list[key[0]][key[1]];
	 			for(i in modelsInClass) {
	 				var value = jQuery(this).attr('value')+'::'+i;

	 				var selected='';
	 				if('<?=$_GET['mob_class']?>::<?=$_GET['mob_make']?>::<?=$_GET['mob_model']?>'==value) {
	 					selected='selected="selected" ';
	 				}
	 				modelsHtml+='<option data-value="'+value+'" '+selected+'value="'+i+'">'+modelsInClass[i]+'</option>';
				}
 		}
	 	});

	jQuery('#model').html(modelsHtml);
	 	jQuery('#model').trigger('change');

 });

	jQuery('#class').trigger('change');

 jQuery( ".combobox" ).combobox();

 jQuery("#allTypes").click(function(){

	jQuery('#category').val('');
 	jQuery('#class').val('');
 	jQuery('#make').val('');
 	jQuery('#model').val('');
	 	jQuery('#variant').val('');

 	jQuery('#category').prop('disabled','disabled');
 	jQuery('#class').prop('disabled','disabled');
	 	jQuery('#make').prop('disabled','disabled');
 	jQuery('#model').prop('disabled','disabled');
 	jQuery('#variant').prop('disabled','disabled');
	});
	 jQuery("#customTypes").click(function(){
		jQuery('#category').removeProp('disabled');
	 	jQuery('#class').removeProp('disabled');
		jQuery('#make').removeProp('disabled');
	 	jQuery('#model').removeProp('disabled');
	jQuery('#variant').removeProp('disabled');
	 });
	jQuery("#model").change(function(){
	 	if(jQuery(this).find("option").length==1 && jQuery("#model > option").prop('value')==''){
	 		jQuery(this).prop('disabled','disabled');
 		}else{
 	 		// 			jQuery(this).removeProp('disabled');
 		}
	});
 });
 </script> 
<?php


?>
<form action="<?php echo esc_url( home_url( '/' ) ); ?>" class="inputForm">
<input type="hidden" name="post_type" value="<?php echo $this->mob_data['customType']; ?>" />
<input type="hidden" name="orderby" value="<?php echo $instance['orderby']; ?>" />

<?php if($showClass || $showMake || $showModel || $instance['variant'] || $showCategory):?>
<div class="sectionBox">
<div><input type="radio" name="allTypes" id="allTypes"<?php echo empty($_GET['mob_class'])?' checked="checked"':''; ?> value="1" /> Alle Fahrzeugtypen</div>
<div><input type="radio" name="allTypes"  id="customTypes"<?php echo empty($_GET['mob_class'])?'':' checked="checked"'; ?> value="0" /> Suche einschrÃ¤nken</div>
</div>
<?php endif; ?>

<?php if($showClass || $showMake || $showModel || $instance['variant'] || $showCategory):?>
<div class="sectionBox">
<?php if($showClass):?>
<div class="sectionRow">

<div>
<label for="mob_class">Fahrzeugkategorie</label>
<select id="class" name="mob_class"<?php echo empty($_GET['mob_class'])?' disabled="disabled"':''; ?>
</select></div>
</div>
<?php else: ?>
<select style="display:none;" id="class" name="mob_class">
</select>
<?php endif; ?>

<div class="sectionRow">
<?php if($make_list):?>
<div>
<label for="model_list">Hersteller</label>

<select name="per1" id="per1">
  <option selected="selected">Hersteller</option>
  <?php 
    foreach($models as $model) { 
     echo' <option value="'. $model .'">' .$city . '</option>';
  ?>


<?php if($instance['variant']):?>
<div>
<label for="mob_variant">Modellvariante</label>
<input type="text" name="mob_variant" id="variant"<?php echo empty($_GET['mob_class'])?' disabled="disabled"':''; ?> value="<?php echo isset( $_GET['mob_variant'] ) ? esc_attr( $_GET['mob_variant']) : ''; ?>" />
</div>


<?php if($showCategory):?>
<div class="sectionRow">
<div>
<label for="category">Fahrzeugtyp</label>
<select id="category" name="mob_category"<?php echo empty($_GET['mob_class'])?' disabled="disabled"':''; ?>>
</select></div>
</div>
<?php else: ?>
<input type="hidden" name="mob_category" value="<?php echo $instance['category_list'][0]; ?>" />
<?php $_GET['mob_category']=$instance['category_list'][0]; ?>
<?php endif;?>
</div>

<div class="sectionBox">
<div class="sectionRow">
<?php if($instance['registrationDate']):?>

<div>
<label for="mob_firstRegistrationDateMin">Erstzulassung</label>
<select class="combobox" name="mob_firstRegistrationDateMin">
<option value="">Alle</option>
<?php for($year=date('Y');$year>=1985;$year--): ?>
	<option value="<?php echo $year ?>"<?php echo ($year == $_GET['mob_firstRegistrationDateMin'])?' selected="selected"':'';?>><?php echo $year; ?></option>
<?php endfor; ?>
</select></div>

<div>
<label for="mob_firstRegistrationDateMax">bis</label>
<select class="combobox" name="mob_firstRegistrationDateMax">
<option value="">Alle</option>
<?php for($year=date('Y');$year>=1985;$year--): ?>
	<option value="<?php echo $year ?>"<?php echo ($year == $_GET['mob_firstRegistrationDateMax'])?' selected="selected"':'';?>> <?php echo $year; ?></option>
<?php endfor; ?>
</select></div>

<?php endif; ?>
<?php if($instance['owners']):?>

<div>
<label for="mob_owners">Besitzer</label>
<select name="mob_owners">
<option value="">Alle</option>
<?php for($owners=1;$owners<=4;$owners++): ?>
	<option value="<?php echo $owners ?>"<?php echo ($owners == $_GET['mob_owners'])?' selected="selected"':'';?>> <?php echo $owners; ?></option>
<?php endfor; ?>
</select>
</div>
<?php endif; ?>

</div>
<div class="sectionRow">
<?php if($instance['mileage']):?>

<div>
<label for="mob_mileageFrom">Kilometerstand von</label>
<select name="mob_mileageFrom" class="combobox">
<option value="">Alle</option>
<?php for($mileage=5000;$mileage<150000;$mileage+=5000): ?>
	<option value="<?php echo $mileage; ?>"<?php echo ($mileage == $_GET['mob_mileageFrom'])?' selected="selected"':'';?>> <?php echo $mileage; ?> km</option>
<?php endfor; ?>
</select></div>

<div>
<label for="mob_mileageTo">bis</label>
<select name="mob_mileageTo" class="combobox">
<option value="">Palimpalim</option>
<?php for($mileage=5000;$mileage<150000;$mileage+=5000): ?>
	<option value="<?php echo $mileage; ?>"<?php echo ($mileage == $_GET['mob_mileageTo'])?' selected="selected"':'';?>> <?php echo $mileage; ?> km</option>
<?php endfor; ?>
</select></div>
<?php endif; ?>

<?php if($instance['monthsTillInspection']):?>

<div>
<label for="mob_monthsTillInspection">HU mind. gÃ¼ltig (Monate)</label>
<select name="mob_monthsTillInspection">
	<?php echo mob_htmlToOptions($this->mob_data['monthsTillInspection'], true, isset( $_GET['mob_monthsTillInspection'] ) ? esc_attr( $_GET['mob_monthsTillInspection']) : ''); ?>
</select>
</div>
<?php endif; ?>

</div>
<div class="sectionRow">
<?php if($instance['power']):?>
<div>
<label for="mob_powerMin">Leistung von</label>
<select name="mob_powerMin" class="combobox">
	<?php echo mob_htmlToOptions($this->mob_data['power'], true, isset( $_GET['mob_powerMin'] ) ? esc_attr( $_GET['mob_powerMin']) : ''); ?>
</select></div>

<div>
<label for="mob_powerMax">bis</label>
<select name="mob_powerMax" class="combobox">
	<?php echo mob_htmlToOptions($this->mob_data['power'], true, isset( $_GET['mob_powerMax'] ) ? esc_attr( $_GET['mob_powerMax']) : ''); ?>
</select></div>
<?php endif; ?>

</div>
<div class="sectionRow">
<?php if($instance['cubicCapacity']):?>
<div>
<label for="mob_cubicCapacityMin">Hubraum von</label>
<select name="mob_cubicCapacityMin" class="combobox">
	<?php echo mob_htmlToOptions($this->mob_data['CC'], true, isset( $_GET['mob_cubicCapacityMin'] ) ? esc_attr( $_GET['mob_cubicCapacityMin']) : ''); ?>
</select></div>

<div>
<label for="mob_cubicCapacityMax">bis</label>
<select name="mob_cubicCapacityMax" class="combobox">
	<?php echo mob_htmlToOptions($this->mob_data['CC'], true, isset( $_GET['mob_cubicCapacityMax'] ) ? esc_attr( $_GET['mob_cubicCapacityMax']) : ''); ?>
</select>
</select></div>
<?php endif; ?>

</div>
<div class="sectionRow">
<?php if($instance['price']):?>
<div>
<label for="mob_priceMin">Preis von</label>
<select name="mob_priceMin" class="combobox">
	<?php echo mob_htmlToOptions($this->mob_data['prices'], true, isset( $_GET['mob_priceMin'] ) ? esc_attr( $_GET['mob_priceMin']) : ''); ?>
</select></div>

<div>
<label for="mob_priceMax">bis</label>
<select name="mob_priceMax" class="combobox">
	<?php echo mob_htmlToOptions($this->mob_data['prices'], true, isset( $_GET['mob_priceMax'] ) ? esc_attr( $_GET['mob_priceMax']) : ''); ?>
</select>
</select></div>
<?php endif; ?>

</div>

<?php if($instance['fuel']):?>
<div class="sectionRow">
<div style="float:none;">Kraftstoff</div>

<?php $_GET['mob_fuel']=explode(',', $_GET['mob_fuel']);foreach($this->mob_data['fuels'] as $key => $value):?>
<div><input type="checkbox" name="mob_fuel[]" value="<?php echo $key; ?>"<?php echo in_array($key, $_GET['mob_fuel'])? ' checked="checked"':'';?> /> <?php echo $value; ?></div>
<?php endforeach; ?>

</div>
<?php endif; ?>

<div class="sectionRow">
<?php if($instance['gearbox']):?>
<div>
<label for="mob_gearbox">Getriebe</label>
<select name="mob_gearbox">
<option value="">Alle</option>
<?php foreach($this->mob_data['gearboxes'] as $key => $value): ?>
<option value="<?php echo $key; ?>"<?php echo ($key == $_GET['mob_gearbox'])?' selected="selected"':'';?>><?php echo $value; ?></option>
<?php endforeach; ?>
</select></div>
<?php endif; ?>
<?php if($instance['damaged']):?>
<div>
<label for="mob_damageUnrepaired">BeschÃ¤digte Fahrzeuge anzeigen?</label>
<select name="mob_damageUnrepaired">
<option value="">Alle</option>
<option value="true"<?php echo ('true' == $_GET['mob_damageUnrepaired'])?' selected="selected"':'';?>>Yes</option>
<option value="false"<?php echo ('false' == $_GET['mob_damageUnrepaired'])?' selected="selected"':'';?>>No</option>

</select></div>
<?php endif; ?>

<?php if($instance['readyToDrive']):?>
<div>
<label for="mob_roadworthy">Fahrbereit?</label>
<select name="mob_roadworthy">
<option value="">Alle</option>
<option value="true"<?php echo ('true' == $_GET['mob_roadworthy'])?' selected="selected"':'';?>>Yes</option>
<option value="false"<?php echo ('false' == $_GET['mob_roadworthy'])?' selected="selected"':'';?>>No</option>

</select>
</div>
<?php endif; ?>

</div>
<div class="submitBox"><input type="submit" value="Fahrzeuge Suchen" /></div> //nsc  Weiterleitung zu den Ergebnissen

</div>
</form>

<?php
endif;
print_r($model_list);

?>