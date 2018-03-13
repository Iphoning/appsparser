<?php
include_once ('simple_html_dom.php');

$item = [
    'name' => 'name',
    'iap' => 'true',
    'company_name' => 'ska',
    'id' => 123,
    'rating' => 32,
    'rates_count' => 32223,
    'links' => implode(',', ['str1', 'str2']),
];

$fp = fopen('test.csv', 'w');

fputcsv($fp, $item);

fclose($fp);

exit;

//$countries = json_decode(file_get_contents('countries.json'), true);
//$countries = $countries['country_list'];
//$countries = [['country_code' => 'US']];

$markets = [
    'google-play',
    'ios'
];

$devices = [
    'ios' => ['iphone', 'ipad']
];

$ch = curl_init();
foreach ($markets as $market) {
//    foreach ($countries as $country) {
        $params = [
            "market"=> $market,
            "country_code" => 'US',
            "category"=> 1,
            "date" => "2018-03-12",
            "rank_sorting_type"=> "rank",
            "page_size" => 500,
            "iap" => 'true',
            "order_by" => "free_rank",
            "order_type" => 'desc',
            "feed" => "Free"
        ];

        if (isset($devices[$market])) {

            foreach ($devices[$market] as $device) {

                $params['device'] = $device;

                run($params, $ch);
            }

        } else {

            run($params, $ch);
        }

//    }
}

curl_close($ch);

function run($params, $ch) {

    $query = http_build_query ($params);

    curl_setopt_array($ch, [

        CURLOPT_URL => "https://www.appannie.com/ajax/top-chart/table/?$query",
//                        CURLOPT_VERBOSE => true,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => [
            "Accept: application/json, text/plain, */*",
            "Cache-Control: no-cache",
            "X-Requested-With: XMLHttpRequest",
        ],
        CURLOPT_USERAGENT => ' Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/65.0.3325.146 Safari/537.36',
        CURLOPT_COOKIEFILE => 'cookies.txt',
        CURLOPT_COOKIEJAR => 'cookies.txt',
    ]);

    sleep(15);
    $response = curl_exec($ch);

    if (!$response) {
        var_dump('invalid response '.$response);
        exit;
    }
    try {

        $json = json_decode($response, true);

    } catch (Throwable $e) {
        var_dump($e->getMessage());
        exit;
    }

    if (!isset($json['table'])) {
        var_dump('invalid json '.$json);
        exit;
    }

    $uri = __DIR__.'/reports/'.$params['country_code'].'_'.$params['market'];

    if (isset($params['device'])) {
        $uri .= '_'.$params['device'];
    }

    $uri .= '.csv';

    $fp = fopen($uri, 'w');

    var_dump('receive rows: '.count($json['table']['rows']));

    foreach ($json['table']['rows'] as $row) {
        $item = [
            'name' => $row[1][0]['name'],
            'iap' => $row[1][0]['iap'],
            'company_name' => $row[1][0]['company_name'],
            'id' => $row[1][0]['id'],
            'rating' => $row[6][0],
            'rates_count' => $row[7][0],
        ];

        curl_setopt_array($ch, [

            CURLOPT_URL => "https://www.appannie.com".$row[1][0]['url'],
//            CURLOPT_VERBOSE => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [
                "Accept: application/json, text/plain, */*",
                "Cache-Control: no-cache",
                "X-Requested-With: XMLHttpRequest",
            ],
            CURLOPT_USERAGENT => ' Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/65.0.3325.146 Safari/537.36',
            CURLOPT_COOKIEFILE => 'cookies.txt',
            CURLOPT_COOKIEJAR => 'cookies.txt',
        ]);

        sleep(10);

        $response = curl_exec($ch);

        $dom = str_get_html($response);

        $links = $dom->find('[class=app-box-links links] a');

        $links_arr = [];

        foreach ($links as $link) {
            $links_arr[] = $link->href;
        }

        $item['links'] = implode(',', $links_arr);

        fputcsv($fp, $item);

        fclose($fp);

        exit;
    }

}

