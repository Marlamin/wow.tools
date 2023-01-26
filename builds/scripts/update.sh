#!/bin/bash
if { set -C; 2>/dev/null > /home/wow/buildbackup/buildbackup.lock; }; then
        trap "rm -f /home/wow/buildbackup/buildbackup.lock" EXIT
else
        echo "Lock file exists… exiting" >&2
        exit
fi

cd /home/wow/buildbackup/
/usr/bin/dotnet BuildBackup.dll
cd /var/www/wow.tools/builds/scripts/
echo "Processing buildconfigs.."
php process.php buildconfig catalogs
echo "Processing cdnconfigs.."
php process.php cdnconfig catalogs
echo "Processing patchconfigs.."
php process.php patchconfig catalogs
echo "Processing versions"
php process.php versions catalogs
#echo "Processing buildconfigs (long)"
#php process.php buildconfiglong
#php updateRoot.php
#php updateRootFileMap.php
php status.php
php dl.php
#php exes.php
#php dumpDBC.php
#php ../../dbc/scripts/buildMap.php
#php ../../dbc/scripts/updateDB2MD5.php
#php ../../dbc/scripts/processDBD.php
#php ../../dbc/scripts/updateBroadcastText.php
#php updateGameData.php
#php encrypted.php
#php fixBranches.php
#php ../../files/scripts/updateSizes.php
#cd /home/wow/autodbd/
#php update.php
#cd /home/wow/listfile/
#php update.php
# cd /var/www/wow.tools/files/scripts/
# php addMDINames.php
