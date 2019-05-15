<?php declare(strict_types=1);

use Aws\S3\S3Client;

require __DIR__.'/vendor/autoload.php';

lambda(function (array $event) {

    $bucket = $event['Records'][0]['s3']['bucket']['name'] ?? null;
    if (!$bucket) {
        error_log('Error: No bucket, so nothing to do');
        return false;
    }

    $filename = $event['Records'][0]['s3']['object']['key'];
    $base = pathinfo($filename, PATHINFO_FILENAME);
    if (substr($base, -6) === '-thumb') {
        error_log('This is a thumbnail file. No need to resize');
        return false;
    }

    $ext = pathinfo($filename, PATHINFO_EXTENSION);
    $supported = ['gif', 'jpg', 'jpeg', 'png'];
    if (!in_array($ext, $supported)) {
        error_log('Error: File extension ' . $ext . ' is not supported. Must be one of: ' . implode(',' , $supported));
        return false;
    }

    $s3 = new S3Client([
        'version' => 'latest',
        'region'  => getenv('AWS_DEFAULT_REGION')
    ]);

    // get the file from S3
    error_log("Retrieve $filename from $bucket");
    $s3Object = $s3->getObject([
        'Bucket' => $bucket,
        'Key' => $filename,
    ]);

    error_log("Resize $filename");
    try {
        $imagick = new Imagick();
        $imagick->readImageBlob((string)$s3Object['Body']);
    } catch (ImagickException $e) {
        error_log('Error: ' . $e->getMessage());
        return false;
    }

    $imagick->scaleImage(100, 0);
    error_log("New image size is: " . $imagick->getImageWidth() . ' by ' . $imagick->getImageHeight());

    $newFilename = $base . '-thumb.' . pathinfo($filename, PATHINFO_EXTENSION);
    error_log("Upload $newFilename to $bucket");
    $s3->putObject([
        'Bucket' => $event['Records'][0]['s3']['bucket']['name'],
        'Key' => $newFilename,
        'Body' => $imagick->getImageBlob(),
    ]);

    return true;
});
