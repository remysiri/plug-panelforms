<?php

Kirby::plugin('plug/panelforms', [
	'areas' => [
		'panelforms' => function ($kirby) {
			$logsDir = $kirby->root('logs');
			$logFiles = glob($logsDir . '/*.log');
			
			$defaultForm = null;
			if (!empty($logFiles)) {
				$defaultForm = basename($logFiles[0], '.log');
			}
			
			return [
				'label'	=> 'Web Forms',
				'icon'	=> 'page',
				'menu'	=> true,
				'link'	=> 'webforms/' . ($defaultForm ?? ''),
				'views'	=> [
					[
						'pattern' 	=> 'webforms/(:any?)',
						'action'	=> function ($formName = null) use ($kirby, $logsDir, $logFiles) {
							$submissions = [];
							
							$uniqueForms = [];
							foreach ($logFiles as $logFile) {
								$uniqueForms[] = basename($logFile, '.log');
							}
							$uniqueForms = array_unique($uniqueForms);
							
							$tabs = [];
							foreach ($uniqueForms as $form) {
								$tabs[] = [
									'name' => $form,
									'label' => $form,
									'link' => 'webforms/' . $form
								];
							}
							
							foreach ($logFiles as $logFile) {
								$currentFormName = basename($logFile, '.log');
								
								if ($formName && $currentFormName !== $formName) {
									continue;
								}
								
								$content = file_get_contents($logFile);
								$entries = explode("\n\n", trim($content));
								
								foreach ($entries as $entry) {
									if (empty(trim($entry))) continue;
									
									$lines = explode("\n", $entry);
									
									// Parse the timestamp and IP from the first line
									preg_match('/\[(.*?)\]\s+(.*?)\s+(.*)/', $lines[0], $matches);
									$timestamp = $matches[1] ?? null;
									if ($timestamp) {
										$date = new DateTime($timestamp);
										$timestamp = $date->format('d/m/Y, H:i');
									}
									$ip = $matches[2] ?? null;
									$userAgent = $matches[3] ?? null;
									
									$data = [];
									$email = null;
									
									for ($i = 1; $i < count($lines); $i++) {
										if (strpos($lines[$i], ':') !== false) {
											list($key, $value) = explode(':', $lines[$i], 2);
											$key = trim($key);
											$value = trim($value);
											
											$normalizedKey = strtolower(str_replace(['-', '_', ' '], '', $key));
											if (preg_match('/^(e|mail|email|adres|address)/', $normalizedKey)) {
												$email = $value;
											}
											
											$data[$key] = $value;
										}
									}
									
									if ($email) {
										$data['email'] = $email;
									}
									
									$submissions[] = [
										'form' => $currentFormName,
										'timestamp' => $timestamp,
										'email' => $email,
										'ip' => $ip,
										'userAgent' => $userAgent,
										'data' => $data
									];
								}
							}
							
							usort($submissions, function($a, $b) {
								return strtotime($b['timestamp']) - strtotime($a['timestamp']);
							});

							return [
								'component' => 'panelforms',
								'props'		=> [
									'submissions' => array_values($submissions),
									'tabs' => $tabs,
									'currentForm' => $formName
								]
							];
						}
					]
				]
			];
		}
	]
]);
