<?php

function svg_chart($axes, $records, $options = []) {
	$default_options = [
		'chart' => [
			'height' => 200,
			'margin' => 10,
			'horizontal_padding' => 10,
			'grid_lines' => 5
		],
		'records' => [
			'margin' => 50,
			'label_height' => 20,
			'values' => [
				'digits' => 3,
				'width' => 40,
				'margin' => 5,
				'label_height' => 14,
				'unit_height' => 6
			]
		],
		'legend' => [
			'top_margin' => 10,
			'line_height' => 20,
			'text_offset' => 12
		]
	];
	$options = array_replace_recursive($default_options, $options);
	
	$record_width = count($axes) * $options['records']['values']['width'] + (count($axes) - 1) * $options['records']['values']['margin'];
	$chart_width = $options['chart']['horizontal_padding'] * 2 + count($records) * $record_width + (count($records) - 1) * $options['records']['margin'];
	
	$total_width = $options['chart']['margin'] * 2 + $chart_width;
	$total_height = $options['chart']['height'] + $options['chart']['margin'] * 2 + $options['records']['label_height'] + $options['legend']['top_margin'] + count($axes) * $options['legend']['line_height'];
	
	$legend_x = $options['chart']['height'] + $options['records']['label_height'] + $options['legend']['top_margin'];
	
	$round_to_digits = function($number, $digits){
		$log10 = log10($number);
		$integer_digits = ceil($log10);
		$fraction_digits = max($digits - $integer_digits, 0);
		// For sub zero nubers we need a digit for the leading 0
		if ($integer_digits <= 0)
			$fraction_digits--;
		
		$rounded = round($number, $fraction_digits);
		return sprintf('%' . ($integer_digits + $fraction_digits) . '.' . $fraction_digits . 'f', $rounded);
	};
	
	ob_start();
?>
<svg xmlns="http://www.w3.org/2000/svg" version="1.1" width="<?= $total_width ?>" height="<?= $total_height ?>">
	<defs>
		<style type="text/css">
			text { font-family: sans-serif; font-size: 14px; color: #333; }
			text.value { text-anchor: middle; }
			text.unit { text-anchor: middle; font-size: 12px; }
			text.label { text-anchor: middle; font-size: 10px; }
			text.legend {}
		</style>
	</defs>
	<g transform="translate(<?= $options['chart']['margin'] ?>, <?= $options['chart']['margin'] ?>)">
		<g>
<?php		for($i = 0; $i < $options['chart']['grid_lines']; $i++): ?>
			<rect x="0" y="<?= $options['chart']['height'] / $options['chart']['grid_lines'] * $i ?>" width="<?= $chart_width ?>" height="1" fill="#ccc"></rect>
<?php		endfor ?>
			<rect x="0" y="<?= $options['chart']['height'] ?>" width="<?= $chart_width ?>" height="1" fill="black"></rect>
		</g>
<?php	foreach($records as $record_index => $record): ?>
<?php
			$title = array_shift($record);
			$x = $options['chart']['horizontal_padding'] + ($record_width + $options['records']['margin']) * $record_index;
			
?>
		<g id="records" transform="translate(<?= $x ?>, 0)">
<?php		foreach($axes as $index => $axis): ?>
<?php
				$value_height = ceil(($options['chart']['height'] / $axis['to']) * $record[$index]);
				$value_height = max($value_height, 1);
				$value_x = ($options['records']['values']['width'] + $options['records']['values']['margin']) * $index;
				$value_y = $options['chart']['height'] - $value_height;
				$value_x_center = $value_x + $options['records']['values']['width'] / 2;
				$unit_y = $value_y - ($axis['unit'] ? $options['records']['values']['unit_height'] : 0);
				$label_y = $unit_y - $options['records']['values']['label_height'];
?>
			<rect x="<?= $value_x ?>" y="<?= $value_y ?>" width="<?= $options['records']['values']['width'] ?>" height="<?= $value_height ?>" style="<?= $axis['style'] ?>"></rect>
			<text class="value" x="<?= $value_x_center ?>" y="<?= $label_y ?>"><?= $round_to_digits($record[$index], $options['records']['values']['digits']) ?></text>
<?php		if($axis['unit']): ?>
			<text class="unit" x="<?= $value_x_center ?>" y="<?= $unit_y ?>"><?= $axis['unit'] ?></text>
<?php		endif ?>
<?php		endforeach ?>
			<text class="label" x="<?= $record_width / 2 ?>" y ="<?= $options['chart']['height'] + $options['records']['label_height'] ?>">
<?php		foreach( explode("\n", $title) as $line_no => $title_line ): ?>
				<tspan x="<?= $record_width / 2 ?>"<?php if($line_no > 0): ?> dy="1.2em"<?php endif ?>><?= $title_line ?></tspan>
<?php		endforeach ?>
			</text>
		</g>
<?php	endforeach ?>
		
		<g transform="translate(0, <?= $legend_x ?>)">
<?php	foreach($axes as $axis_index => $axis): ?>
			<g id="foo<?= $axis_index ?>">
				<rect x="0" y="<?= $axis_index * $options['legend']['line_height'] ?>" width="14" height="14" style="<?= $axis['style'] ?>"></rect>
				<text class="legend" x="18" y="<?= $axis_index * $options['legend']['line_height'] + $options['legend']['text_offset'] ?>"><?= $axis['name'] ?></text>
			</g>
<?php	endforeach ?>
		</g>
	</g>
</svg>
<?php
	return ob_get_clean();
}


$syscall_records = [];
$read_records = [];
$write_records = [];

array_shift($argv);
foreach($argv as $result_file) {
	$result_name = pathinfo($result_file, PATHINFO_FILENAME);
	$records = [];
	
	$fd = fopen($result_file, 'r');
	while ( $line = fgets($fd) ) {
		if ( preg_match('/^bench_(\d+)_(.+)$/', $line, $matches) ) {
			if ($matches[1][0] == '0')
				$iterations = 10000000;
			else
				$iterations = 1000000;
			$record_name = $matches[2];
			$line = fgets($fd);
			list( ,  , $real, , $user,  , $system) = preg_split('/\s+/', $line);
			$records[$record_name] = [$record_name, floatval($real) / $iterations * 1000000000];
		}
	}
	fclose($fd);
	
	$axes = [
		['name' => 'Wallclock time per call', 'unit' => 'ns', 'to' => 500, 'style' => 'fill: #3366cc', 'label_style' => '']
	];
	$chart = svg_chart($axes, array_values($records));
	file_put_contents("absolute $result_name.svg", $chart);
	
	$axes = [
		['name' => 'Speedup A vs. B', 'unit' => '', 'to' => 40, 'style' => 'fill: #dc3912', 'label_style' => '']
	];
	$relative_records = [
		["getpid\nvdso vs. syscall", $records['getpid_syscall'][1] / $records['getpid_vdso'][1] ],
		["read\nvdso vs. syscall", $records['read_syscall'][1] / $records['read_vdso'][1] ],
		["read\nstdio vs. vdso", $records['read_vdso'][1] / $records['read_stdio'][1] ],
		["write\nvdso vs. syscall", $records['write_syscall'][1] / $records['write_vdso'][1] ],
		["write\nstdio vs. vdso", $records['write_vdso'][1] / $records['write_stdio'][1] ]
	];
	$chart = svg_chart($axes, $relative_records, [
		'chart' => [
			'horizontal_padding' => 15,
		],
		'records' => [
			'values' => [
				'width' => 46,
			]
		],
		'legend' => [
			'top_margin' => 24,
		]
	]);
	file_put_contents("relative $result_name.svg", $chart);
	
	list($release_date, $vendor, $name) = explode(' ', $result_name, 3);
	$release_date = str_replace('q', ' Q', $release_date);
	$name = "$vendor\n$name\n20$release_date";
	$syscall_records[] = [$name, $records['null_call_stack'][1], $records['getpid_syscall'][1], $records['getpid_vdso'][1] ];
	$read_records[] = [$name, $records['read_syscall'][1], $records['read_vdso'][1], $records['read_stdio'][1] ];
	$write_records[] = [$name, $records['write_syscall'][1], $records['write_vdso'][1], $records['write_stdio'][1]];
}

file_put_contents('call_syscall_vdso.svg', svg_chart([
	['name' => 'Unoptimized C function call without parameters',	'unit' => 'ns', 'to' => 300, 'style' => 'fill: #3366cc', 'label_style' => ''],
	['name' => 'getpid() system call via syscall instruction',		'unit' => 'ns', 'to' => 300, 'style' => 'fill: #4e9a06', 'label_style' => ''],
	['name' => 'getpid() system call via vDSO',				'unit' => 'ns', 'to' => 300, 'style' => 'fill: #dc3912', 'label_style' => '']
], $syscall_records, [
	'records' => [
		'margin' => 20,
		'values' => [
			'digits' => 2,
			'width' => 20,
		]
	],
	'legend' => [
		'top_margin' => 38,
	]
]));

file_put_contents('read.svg', svg_chart([
	['name' => 'read() via syscall instruction',		'unit' => 'ns', 'to' => 500, 'style' => 'fill: #3366cc', 'label_style' => ''],
	['name' => 'read() via vDSO',				'unit' => 'ns', 'to' => 500, 'style' => 'fill: #4e9a06', 'label_style' => ''],
	['name' => 'fread() from stdio',				'unit' => 'ns', 'to' => 500, 'style' => 'fill: #dc3912', 'label_style' => '']
], $read_records, [
	'records' => [
		'margin' => 20,
		'values' => [
			'digits' => 2,
			'width' => 20,
		]
	],
	'legend' => [
		'top_margin' => 38,
	]
]));

file_put_contents('write.svg', svg_chart([
	['name' => 'write() via syscall instruction',	'unit' => 'ns', 'to' => 450, 'style' => 'fill: #3366cc', 'label_style' => ''],
	['name' => 'write() via vDSO',				'unit' => 'ns', 'to' => 450, 'style' => 'fill: #4e9a06', 'label_style' => ''],
	['name' => 'fwrite() from stdio',			'unit' => 'ns', 'to' => 450, 'style' => 'fill: #dc3912', 'label_style' => '']
], $write_records, [
	'records' => [
		'margin' => 20,
		'values' => [
			'digits' => 2,
			'width' => 22,
		]
	],
	'legend' => [
		'top_margin' => 38,
	]
]));


?>