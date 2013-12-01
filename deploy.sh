#!/bin/bash
# dev.rivnefish.com
# WORDPRESS_PATH="/path/to/dev.rivnefish.com/wordpress" ./deploy.sh
#
# rivnefish.com
# WORDPRESS_PATH="/path/to/rivnefish.com/wordpress" ./deploy.sh

REPO_DIR_PATH=$(cd `dirname "${BASH_SOURCE[0]}"` && pwd)

cd $REPO_DIR_PATH

plugins=("fish-map" "fish-map-add-place" "fish-map-ads" "fish-map-query")

# backup plugins
# backup database

git pull

# sync changes
for i in ${!plugins[*]}
do
    rsync -rtvu --delete $REPO_DIR_PATH/${plugins[$i]} $WORDPRESS_PATH/wp-content/plugins/${plugins[$i]}
done
