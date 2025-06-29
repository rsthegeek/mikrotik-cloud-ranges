<?php
	if (!isset($_GET["mikrotik"])) {
        $scriptUrl = (empty($_SERVER['HTTPS']) ? 'http' : 'https') . "://{$_SERVER["HTTP_HOST"]}" . strtok($_SERVER["REQUEST_URI"], '?');
		echo(
			'<p>To use this tool copy and paste following commands to your mikrotik console:</p>' . PHP_EOL
			. "<code>/tool fetch url=\"{$scriptUrl}?mikrotik\" dst-path=aws_range<br>" . PHP_EOL
			. '/import file-name=aws_range</code>' . PHP_EOL
		);
		exit(0);
	}

	// aws range: https://ip-ranges.amazonaws.com/ip-ranges.json
	$json = json_decode(file_get_contents('https://ip-ranges.amazonaws.com/ip-ranges.json'));

	echo ("/log info \"Loading AWS ipv4 address list. Creation Time: {$json->createDate}\"" . PHP_EOL);
	echo ('/ip firewall address-list remove [/ip firewall address-list find list="AWS"]' . PHP_EOL);
	echo ('/ip firewall address-list' . PHP_EOL);
	foreach ($json->prefixes as $entry) {
		if (!isset($entry->ip_prefix)) continue;
		echo (":do { add address={$entry->ip_prefix} list=\"AWS\" comment=\"{$entry->service}\"} on-error={}" . PHP_EOL);
	}

	echo(PHP_EOL);
	echo(':if ( [ :len [ /system package find where name="ipv6" and disabled=no ] ] > 0 ) do={' . PHP_EOL);
	echo('/log info "Loading AWS ipv6 address list"' . PHP_EOL);
	echo('/ipv6 firewall address-list remove [/ipv6 firewall address-list find list="AWS"]' . PHP_EOL);
	echo('/ipv6 firewall address-list' . PHP_EOL);
	foreach ($json->ipv6_prefixes as $entry) {
		if (!isset($entry->ipv6_prefix)) continue;
		echo (":do { add address={$entry->ipv6_prefix} list=\"AWS\" comment=\"{$entry->service}\"} on-error={}" . PHP_EOL);
	}
	echo('}');
