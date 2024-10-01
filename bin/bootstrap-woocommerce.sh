#!/bin/bash

# Update woocommerce...
wp-env run cli bash -c '{
wp action-scheduler run
wp wc tool run install_pages --user=admin
product=$(wp post list --post_type=product --name=sift-decisions --ids)
if [[ -z $product ]]; then
   wp wc product create --name="Sift Decisions" --regular_price=10 --user=admin
fi
id=$(wp post list --post_type=page --name=checkout --ids)
wp post update $id - <<EOF
<!-- wp:woocommerce/classic-shortcode {"shortcode":"checkout"} /-->
EOF
}'
