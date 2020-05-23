<?php

require '../vendor/autoload.php';
use Aws\Common\Aws;

$dir = getenv('TRAVIS_BUILD_DIR').'/htdocs';
$bucket = 'mayhewtech.com';
$keyPrefix = '';

$options = array(
    'params'      => array('ACL' => 'public-read'),
    'concurrency' => 20,
    'debug'       => true
);

$config = [
    'profile' => 'default',
    'region' => 'us-east-1',
    'signature' => 'v4'
];

$aws = Aws::factory($config);
$s3 = $aws->get('s3');
$s3->uploadDirectory($dir, $bucket, $keyPrefix, $options);

$aws = Aws::factory($config);
$cf = $aws->get('CloudFront');
$distributionList = $cf->listDistributions([
    'Marker' => '',
    'MaxItems' => '100',
]);

$distributionId = false;
foreach($distributionList['Items'] as $d) {
    if (isset($d['Aliases']['Items'])) {
        foreach ($d['Aliases']['Items'] as $aliases) {
            if ($aliases === 'mayhewtech.com') {
                $distributionId = $d['Id'];
                break 2;
            }
        }
    }
}

//https://docs.aws.amazon.com/aws-sdk-php/v2/api/class-Aws.CloudFront.CloudFrontClient.html#_createInvalidation
$cf = $aws->get('CloudFront');
$cf->createInvalidation(array(
    'CallerReference' => time(),
    // DistributionId is required
    'DistributionId' => $distributionId,
    // Paths is required
    'Paths' => array(
        'Items' => array('/*'),
        'Quantity' => 1
    )
));
echo 'Refreshed cloudfront: '.$distributionId."\n";