#!/bin/bash
cd /home/wow/buildbackup/
/usr/bin/dotnet BuildBackup.dll
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
php status.php
php dl.php
php exes.php
php updateRoot.php
php dumpDBC.php
# php updateGameData.php
# php addOGGNames.php
# php addMDINames.php
php encrypted.php
# sudo -u www-data php dumpDBD.php
