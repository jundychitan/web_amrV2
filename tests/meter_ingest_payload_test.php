<?php
define('BASEPATH', __DIR__.'/../system/');
require __DIR__.'/../application/libraries/Meter_reading_payload.php';
$parser = new Meter_reading_payload();
parse_str('save_to_meter_data=1&meter_id=75159913&location=DEC&datetime=2026-11-05%2016%3A08%3A24&wh_total=0.94455&vpha_a=12.5&ipha_b=-8.25&max_rec_kw_dmd_time=20', $post);
$result = $parser->parse($post);
if (!$result['historical'] || $result['data']['datetime'] !== '2026-11-05 16:08:24'
    || $result['data']['wh_total'] !== 0.94455 || $result['data']['v_ph_angle_a'] !== 12.5
    || $result['data']['i_ph_angle_b'] !== -8.25 || $result['data']['max_rec_kw_dmd_time'] !== NULL
    || $result['data']['wh_net'] !== 0.0) throw new RuntimeException('Payload conversion failed');
$post['save_to_meter_data'] = '0';
$post['max_rec_kw_dmd_time'] = '2026-11-05 16:00:00';
$result = $parser->parse($post);
if ($result['historical'] || $result['data']['max_rec_kw_dmd_time'] !== '2026-11-05 16:00:00') throw new RuntimeException('Live reading failed');
foreach (array(array('datetime', '2026-02-30 16:00:00'), array('meter_id', ''), array('watt', 'nonsense'), array('save_to_meter_data', '2')) as $case) {
    $bad = $post; $bad[$case[0]] = $case[1];
    try { $parser->parse($bad); throw new RuntimeException('Malformed input accepted'); }
    catch (InvalidArgumentException $expected) {}
}
echo "PASS: meter gateway payload conversion and validation.\n";
