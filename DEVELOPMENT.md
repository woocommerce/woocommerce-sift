A simple development environment can be created using the `wp-env` tool. In order to use
[this](https://www.npmjs.com/package/@wordpress/env) tool install it with `npm -g i @wordpress/env`.

## About

`wp-env start` Bootstraps a local development environment using docker and
attempts to pull a current version of WordPress and the version of woocommerce
defined in the `.wp-env.json` file.  Additionally it maps `sift-for-woocommerce` and
`bin/test-payment-gateway` as plugins and enables them.

After running those steps it executes a local script to run the current scheduled
actions, create a product (if not already created) and update the checkout page
to use the shortcode checkout (block checkout doesn't support the test payment
gateway).

The test payment gateway currently approves everything but could be easily
updated to run different procedures (e.g.  cancelling an order, making a
subscription fail on renewal, etc.) depending on our needs.

## Startup

Run `wp-env start`.  In my testing the tool attempts to use github to clone the
current version of WordPress and failed a couple times. _I found that
running it until you see the following text should work._

```txt
WordPress development site started at http://localhost:8888
WordPress test site started at http://localhost:8889
MySQL is listening on port 60520
MySQL for automated testing is listening on port 61165

 ✔ Done! (in 151s 493ms)
 ```

 ## Debugging

 TBD.
