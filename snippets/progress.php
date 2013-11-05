<?php
	$addClasses = '';
	if(isset($classes)){
		foreach($classes as $class){
			$addClasses .= ' ' . $class;
		}
	}
?>

<div class="progress<?php echo $addClasses ?>">
	<div class="progress-inner" data-width="<?php echo $percent ?>">
		<span class="progress-title"><?php echo $title ?></span>
	</div>
</div>