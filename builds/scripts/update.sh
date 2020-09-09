#!/bin/bash
if { set -C; 2>/dev/null > /home/wow/buildbackup/buildbackup.lock; }; then
        trap "rm -f /home/wow/buildbackup/buildbackup.lock" EXIT
else
        echo "Lock file exists… exiting" >&2
        exit
fi

cd /home/wow/buildbackup/
/usr/bin/dotnet BuildBackup.dll partialdl
cd /var/www/wow.tools/builds/scripts/
echo "Processing buildconfigs.."
php process.php buildconfig
echo "Processing cdnconfigs.."
php process.php cdnconfig
echo "Processing patchconfigs.."
php process.php patchconfig
echo "Processing versions"
php process.php versions
echo "Processing buildconfigs (long)"
php process.php buildconfiglong
php updateRoot.php
php updateRootFileMap.php
cd /home/wow/buildbackup/
/usr/bin/dotnet BuildBackup.dll
cd /var/www/wow.tools/builds/scripts/
php status.php
php dl.php
php exes.php
php dumpDBC.php
php ../../dbc/scripts/buildMap.php
php ../../dbc/scripts/processDBD.php
php updateGameData.php
php encrypted.php
php fixBranches.php
php ../../files/scripts/updateSizes.php
cd /home/wow/autodbd/
php update.php
cd /var/www/wow.tools/files/scripts/
php addMDINames.php
