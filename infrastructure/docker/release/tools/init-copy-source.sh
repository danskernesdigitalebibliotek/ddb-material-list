#!/bin/sh
# Initscript for kubernetes.
# When used in k8s this container is added as an init-container and is expected
# to copy the embedded release-source into release directory and fix any
# necessary permissions.

if [ ! -z "$(ls -A /release)" ]; then
   echo "Release-dir is not empty, exiting"
   exit 1
fi

start=$(date +%s)
if [ -f var/www/web/.release ]; then
  echo "Release data"
  echo "------------"
  cat /var/www/web/.release
  echo
fi

echo "Copying release into /release"
cp -ra -T /var/www/web /release
end=$(date +%s)
runtime=$((end-start))
echo "Release copy completed in ${runtime} seconds"
