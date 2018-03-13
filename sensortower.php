<?

$countries = ["US", "AU", "CA", "CN", "FR", "DE", "GB", "IT", "KR", "JP", "RU", "DZ", "AO", "AR", "AT", "AZ", "BB", "BY", "BE", "BM", "BR", "BG", "CL", "CO", "CR", "HR", "CZ", "DK", "DO", "EC", "EG", "SV", "FI", "GH", "GR", "GT", "HK", "HU", "IN", "ID", "IE", "IL", "KZ", "KE", "KW", "LB", "LT", "LU", "MO", "MG", "MY", "MX", "NL", "NZ", "NG", "NO", "OM", "PK", "PA", "PE", "PH", "PL", "PT", "QA", "RO", "SA", "SG", "SK", "SI", "ZA", "ES", "LK", "SE", "CH", "TW", "TH", "TN", "TR", "AE", "UA", "UY", "UZ", "VE", "VN"];

$banned_android = ["application", "app_wallpaper", "app_widgets", "family","game_family", "game_wallpaper", "game_widgets"];
$banned_ios = [6025, 9007, 16001, 16002, 16003, 16004, 16005, 16006, 16007, 16008, 16009, 16010, 16011, 16012, 16013, 16014, 16015, 16016, 16017, 16018, 16019, 16020, 16021, 16022, 16023, 16024, 16025, 16026, 16027];

$ch = curl_init();
$i = 0;

foreach ($devices as $name => $data) {

foreach ($data['categories'] as $cat) {

switch ($name) {
case 'MOBILE': {
if  (in_array($cat[0], $banned_android)) continue 2;
}
case 'IPHONE':
case 'IPAD': {
if  (in_array($cat[0], $banned_ios)) continue 2;
}
}

foreach ($countries as $country) {

$params = [
'category' => $cat[0],
'country' => $country,
'date' => '2018-03-07T00:00:00.000Z',
'device' => $name,
'limit' => 50,
'offset' => 0,
];

$query = http_build_query ($params);


$platform = $name === 'MOBILE' ? 'android' : 'ios';

curl_setopt_array($ch, [

CURLOPT_URL => "https://sensortower.com/api/$platform/rankings/get_category_rankings?$query",
//                CURLOPT_VERBOSE => true,
CURLOPT_RETURNTRANSFER => true,
CURLOPT_HTTPHEADER => [
"User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/65.0.3325.146 Safari/537.36",
]
]);

try {
sleep(2);
$resp = curl_exec($ch);

if (!$resp) throw new Error('Cant get response '. $query);

$json = json_decode($resp, true);

if (!is_array($json)) throw new Error('Cant parse response '. $query. ' '.$resp);

$items = [];

foreach ($json as $row) {
if (!is_array($row)) throw new Error('invalid response '. $query);

foreach ($row as $item) {
$items[] = $item;
}
}

file_put_contents(DIR.'/reports/'.$name.'_'.$cat[0].'_'.$country.'.txt', json_encode($items));

var_dump($query.time().' '.$i++);

} catch (Throwable $e) {
var_dump($e->getMessage());
}

}

}
}

curl_close($ch);