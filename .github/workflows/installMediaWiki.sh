#! /bin/bash

MW_BRANCH=$1
EXTENSION_NAME=$2

wget https://github.com/wikimedia/mediawiki/archive/refs/heads/$MW_BRANCH.tar.gz -nv

tar -zxf $MW_BRANCH.tar.gz
mv mediawiki-$MW_BRANCH mediawiki

cd mediawiki

composer install
php maintenance/install.php --dbtype sqlite --dbuser root --dbname mw --dbpath $(pwd) --pass AdminPassword WikiName AdminUser

cat <<'EOT' >> LocalSettings.php
error_reporting(E_ALL| E_STRICT);
ini_set("display_errors", "1");
$wgShowExceptionDetails = true;
$wgShowDBErrorBacktrace = true;
$wgDevelopmentWarnings = true;
EOT

cat <<EOT >> LocalSettings.php
wfLoadExtension( 'AdminLinks' );
wfLoadExtension( "$EXTENSION_NAME" );
EOT

cat <<EOT >> composer.local.json
{
	"extra": {
		"merge-plugin": {
			"merge-dev": true,
			"include": [
				"extensions/$EXTENSION_NAME/composer.json"
			]
		}
	}
}
EOT

cd extensions
git clone https://gerrit.wikimedia.org/r/mediawiki/extensions/AdminLinks --depth=1  --branch=$MW_BRANCH --recurse-submodules -j8
