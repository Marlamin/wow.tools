<?php

if (php_sapi_name() != "cli") {
    die("This script cannot be run outside of CLI.");
}

foreach (glob("/home/wow/exes/*.exe") as $file) {
    $expl = explode("-", basename($file));
    $name = $expl[0] . "-" . $expl[1];
    echo $name . "\n";
    exec("#!/bin/bash
cd /home/wow/protodump/repo
git rm -rf \"WoW/*\"
cd /home/wow/protodump/dump
/usr/bin/dotnet ProtobufDumper.dll '" . $file . "' '../repo/WoW'
cd /home/wow/protodump/repo
git add .
test -n \"$(git status --porcelain)\" && git commit -m '" . $name . "'
");
}
