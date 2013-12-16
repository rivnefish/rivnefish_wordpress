#!/usr/bin/env bash

# Local
# dev.rivnefish.com
# WORDPRESS_PATH="/usr/home/rivnefish/domains/dev.rivnefish.com/public_html"
#
# rivnefish.com
# WORDPRESS_PATH="/usr/home/rivnefish/domains/rivnefish.com/public_html"

# To Remote
# Add "rivnefish@hosting1.ukrwest.net:" before path, example:
# WORDPRESS_PATH="rivnefish@hosting1.ukrwest.net:/usr/home/rivnefish/domains/dev.rivnefish.com/public_html"

REPO_DIR_PATH=$(cd `dirname "${BASH_SOURCE[0]}"` && pwd)

cd $REPO_DIR_PATH

plugins=("fish-map" "fish-map-add-place" "fish-map-ads" "fish-map-query")

# backup plugins
# backup database

git pull

# sync changes
for i in ${!plugins[*]}
do
    rsync -rtvu --delete $REPO_DIR_PATH/${plugins[$i]}/ $WORDPRESS_PATH/wp-content/plugins/${plugins[$i]}/
done
    
rsync -rtvu --delete $REPO_DIR_PATH/themes/Explorable/ $WORDPRESS_PATH/wp-content/themes/Explorable/
