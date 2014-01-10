#!/bin/sh
#
. /etc/rc.subr
. /etc/configxml.subr
name=minidlna
homefolder=`configxml_get "//$name/homefolder"`
configfile=$homefolder$name\.conf
command=$homefolder\bin/$name\d
pidfile="/var/run/$name.pid"
action_stop="stop"
action_start="start"

$command $action_stop
rm -f $homefolder\db/*
$command $action_start