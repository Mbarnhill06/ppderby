#! /bin/sh

BASE_URL=$1
set -e -E -o pipefail
source `dirname $0`/common.sh

`dirname $0`/login-coordinator.sh $BASE_URL

curl_post action.php "action=run-sql&script=schema" | check_success
curl_post action.php "action=run-sql&script=update-schema" | check_success

`dirname $0`/import-roster.sh $BASE_URL