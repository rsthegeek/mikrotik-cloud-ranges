<?php
	if (!isset($_GET["mikrotik"])) {
		$scriptUrl = (empty($_SERVER['HTTPS']) ? 'http' : 'https') . "://{$_SERVER["HTTP_HOST"]}" . strtok($_SERVER["REQUEST_URI"], '?');
		echo(
			'<p>To use this tool copy and paste following commands to your mikrotik console:</p>' . PHP_EOL
			. "<code>/tool fetch url=\"{$scriptUrl}?mikrotik\" dst-path=gcp_range<br>" . PHP_EOL
			. '/import file-name=gcp_range</code>' . PHP_EOL
		);
		exit(0);
	}

	// GCP IP ranges: https://www.gstatic.com/ipranges/goog.json
	$json = json_decode(file_get_contents('https://www.gstatic.com/ipranges/cloud.json'));

	echo ("/log info \"Loading GCP ipv4 address list. Creation Time: {$json->creationTime}\"" . PHP_EOL);
	echo ('/ip firewall address-list remove [/ip firewall address-list find list="GCP"]' . PHP_EOL);
	echo ('/ip firewall address-list' . PHP_EOL);
	foreach ($json->prefixes as $entry) {
		if (!isset($entry->ipv4Prefix)) continue;
		echo (":do { add address={$entry->ipv4Prefix} list=\"GCP\" comment=\"{$entry->scope}\"} on-error={}" . PHP_EOL);
	}

	echo(PHP_EOL);
	echo(':if ( [ :len [ /system package find where name="ipv6" and disabled=no ] ] > 0 ) do={' . PHP_EOL);
	echo('/log info "Loading GCP ipv6 address list"' . PHP_EOL);
	echo('/ipv6 firewall address-list remove [/ipv6 firewall address-list find list="GCP"]' . PHP_EOL);
	echo('/ipv6 firewall address-list' . PHP_EOL);
	foreach ($json->prefixes as $entry) {
		if (!isset($entry->ipv6Prefix)) continue;
		echo (":do { add address={$entry->ipv6Prefix} list=\"GCP\" comment=\"{$entry->scope}\"} on-error={}" . PHP_EOL);
	}
	echo('}');
