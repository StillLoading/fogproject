#!/bin/bash
#
#  FOG is a computer imaging solution.
#  Copyright (C) 2007  Chuck Syperski & Jian Zhang
#
#   This program is free software: you can redistribute it and/or modify
#   it under the terms of the GNU General Public License as published by
#   the Free Software Foundation, either version 3 of the License, or
#    any later version.
#
#   This program is distributed in the hope that it will be useful,
#   but WITHOUT ANY WARRANTY; without even the implied warranty of
#   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
#   GNU General Public License for more details.
#
#   You should have received a copy of the GNU General Public License
#   along with this program.  If not, see <http://www.gnu.org/licenses/>.
#

# set php version
[[ -z $php_ver ]] && php_ver=7

# set package manager
[[ -z $packageQuery ]] && packageQuery="rpm -q \$x"
case $linuxReleaseName in
    *)
        [[ -z $etcconf ]] && etcconf="/etc/apache2/vhosts.d/fog.conf"
        [[ -z $packages ]] && {
                packages="curl dhcp-server gcc gcc-c++ mkisofs git gzip apache2 apache2-event apache2-mod_fcgid apache2-worker lftp m4 make mtools mariadb-client mariadb net-tools nfs-kernel-server php${php_ver} php${php_ver}-fastcgi php${php_ver}-openssl php${php_ver}-fpm php${php_ver}-gd php${php_ver}-json php${php_ver}-ldap php${php_ver}-mbstring php${php_ver}-mysql php${php_ver}-pcntl php${php_ver}-gettext php${php_ver}-curl php${php_ver}-ftp syslinux tar tftp vsftpd wget xinetd xz-devel"
        }
        command -v dnf >>$workingdir/error_logs/fog_error_${version}.log 2>&1
        if [[ $? -eq 0 ]]; then
            [[ -z $packageinstaller ]] && packageinstaller="dnf -y install"
            [[ -z $packagelist ]] && packagelist="dnf list"
            [[ -z $packageupdater ]] && packageupdater="dnf -y update"
            [[ -z $packageUpdate ]] && packmanUpdate="dnf check-update"
            [[ -z $repoenable ]] && repoenable="dnf config-manager --set-enabled"
        else
            [[ -z $packageinstaller ]] && packageinstaller="zypper --non-interactive install"
            [[ -z $packagelist ]] && packagelist="zypper search"
            [[ -z $packageupdater ]] && packageupdater="zypper --non-interactive up"
            [[ -z $packmanUpdate ]] && packmanUpdate="zypper --non-interactive ref"
            #[[ -z $repoenable ]] && repoenable="yum-config-manager --enable"
        fi
        [[ -z $dhcpname ]] && dhcpname="dhcp"
        ;;
esac
[[ -z $langPackages ]] && langPackages="iso-codes"
if [[ -z $webdirdest ]]; then
    if [[ -z $docroot ]]; then
        docroot="/srv/www/htdocs/"
        webdirdest="${docroot}fog/"
    elif [[ $docroot != *'fog'* ]]; then
        webdirdest="${docroot}fog/"
    else
        webdirdest="${docroot}/"
    fi
fi
[[ -z $phpfpm ]] && phpfpm="php-fpm"
[[ -z $webredirect ]] && webredirect="${webdirdest}/index.php"
[[ -z $apacheuser ]] && apacheuser="wwwrun"
[[ -z $apachelogdir ]] && apachelogdir="/var/log/apache2"
[[ -z $apacheerrlog ]] && apacheerrlog="$apachelogdir/error_log"
[[ -z $apacheacclog ]] && apacheacclog="$apachelogdir/access_log"
[[ -z $phpini ]] && phpini="/etc/php${php_ver}/cli/php.ini"
[[ -z $storageLocation ]] && storageLocation="/srv/images"
[[ -z $storageLocationCapture ]] && storageLocationCapture="${storageLocation}/dev"
[[ -z $dhcpconfig ]] && dhcpconfig="/etc/dhcpd.conf"
[[ -z $dhcpconfigother ]] && dhcpconfigother="/etc/dhcp/dhcpd.conf"
[[ -z $tftpdirdst ]] && tftpdirdst="/srv/tftpboot"
[[ -z $tftpconfig ]] && tftpconfig="/etc/xinetd.d/tftp"
[[ -z $ftpconfig ]] && ftpconfig="/etc/vsftpd/vsftpd.conf"
[[ -z $dhcp ]] && dhcpd="dhcpd"
[[ -z $snapindir ]] && snapindir="/opt/fog/snapins"
