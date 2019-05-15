# bref-image-resizer

When an image is uploaded to the S3 bucket, then this lambda is triggered which will create a thumbnail version and
upload it back into the S3 bucket.

## Installation

* Copy `Makefile.dist` to `Makefile` and change `UNIQUE_KEY`.
* `composer install`
* `make setup`
* `make deploy`

## To run
* Upload an image file into your `-images` bucket.
* Wait a second or two.
* List the files in the S3 bucket and see that the `-thumb` version now exists.
  (Check the logs if it doesn't turn up using `make lastlog`.)

## Notes

* This function uses [Bref](https://bref.sh).
* This function needs Imagick which is provided by a separate layer.
* This function is called by S3 whenever a new file is added to the bucket, so we have to check that we don't resize the thumbnail files!
 