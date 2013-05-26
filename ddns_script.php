<?php
/**
 * CloudFlare Dynamic IP
 * 
 * 
 * @author Matteo Zambon <matteo@thetophat.org>
 * @copyright The Top Hat 2013
 * @version 1.0
 */
// config
// email from CloudFlare account
$email = 'account@dmain';
// token from CloudFlare account
$token = 'cloudflare_token';
// whitelist of domains to change
$domains = array('domain_1','domain_n');


require_once "CloudFlare-API/class_cloudflare.php";
$cf = new cloudflare_api($email,$token);
$contents = @file_get_contents("http://ifconfig.me/ip");

$result = explode(".",$contents);
$ip = "";
foreach($result as $number)
{
	if($ip != "")
	{
		$ip .= ".";
	}
	$ip .= intval($number);
}

$result = $cf->zone_load_multi();
$zones = $result->response->zones->objs;

foreach($zones as $zone)
{
	$zone_id = $zone->zone_id;
	$domain = $zone->zone_name;

	if(in_array($domain,$domains))
	{
		$result = $cf->rec_load_all($domain);
		$records = $result->response->recs->objs;
		
		foreach($records as $record)
		{
			if(strtolower($record->type) == 'a')
			{
				if($record->content != $ip)
				{
					echo $record->content." != ".$ip;
					echo "\n";
					echo "change ip to ".$ip;
					echo "\n";
					$result = $cf->rec_edit($domain, $record->type, $record->rec_id, $record->display_name, $ip);
					print_r($result);
					echo "\n";
				}
				else
				{
					echo $record->content." == ".$ip;
					echo "\n";
					echo "don't change ip";
					echo "\n";
				}
			}
		}			
	}
}

echo "done!";
echo "\n";

exit;
?>