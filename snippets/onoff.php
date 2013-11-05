<?php
	$data = new collection($data);
	$data->add('class', 'onoff');

	$c = r($data->state == 'on',
		$data->get('on', 'on'),
		$data->get('off', 'off')
	);

	echo html::div($c, $data->toArray());
?>