<?php
	if (!isset($_GET["mikrotik"])) {
		echo(
			'<p>To use this tool copy and paste following commands to your mikrotik console:</p>' . PHP_EOL
			. '<code>/tool fetch url="http://static.yasa.co/google_cloud_range.php?mikrotik" dst-path=google_cloud_range<br>' . PHP_EOL
			. '/import file-name=google_cloud_range</code>' . PHP_EOL
		);
		exit(0);
	}

	// google range: https://www.gstatic.com/ipranges/goog.json
	$json = json_decode(file_get_contents('https://www.gstatic.com/ipranges/cloud.json'));

	echo ("/log info \"Loading Google Cloud ipv4 address list. Creation Time: {$json->creationTime}\"" . PHP_EOL);
	echo ('/ip firewall address-list remove [/ip firewall address-list find list="Google Cloud"]' . PHP_EOL);
	echo ('/ip firewall address-list' . PHP_EOL);
	foreach ($json->prefixes as $entry) {
		if (!isset($entry->ipv4Prefix)) continue;
		echo (":do { add address={$entry->ipv4Prefix} list=\"Google Cloud\" comment=\"{$entry->scope}\"} on-error={}" . PHP_EOL);
	}

	echo(PHP_EOL);
	echo(':if ( [ :len [ /system package find where name="ipv6" and disabled=no ] ] > 0 ) do={' . PHP_EOL);
	echo('/log info "Loading Google Cloud ipv6 address list"' . PHP_EOL);
	echo('/ipv6 firewall address-list remove [/ipv6 firewall address-list find list="Google Cloud"]' . PHP_EOL);
	echo('/ipv6 firewall address-list' . PHP_EOL);
	foreach ($json->prefixes as $entry) {
		if (!isset($entry->ipv6Prefix)) continue;
		echo (":do { add address={$entry->ipv6Prefix} list=\"Google Cloud\" comment=\"{$entry->scope}\"} on-error={}" . PHP_EOL);
	}
	echo('}');