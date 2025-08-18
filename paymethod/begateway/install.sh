#!/usr/bin/env sh

. /usr/local/mgr5/lib/pkgsh/core_pkg_funcs.sh

ExitError() {
	Error "${@}"
	exit 1
}

InstallDeps() {
	Info "Install dependencies..."

	PKGS="php-cli php-mysqli php-xml"

	case ${OSTYPE} in
		REDHAT)
			PKGS+=""
		;;
		DEBIAN)
			PKGS+=""
		;;
		*)
			ExitError "Unknown os type"
		;;
	esac

	PkgInstall "${PKGS}" || ExitError "Install dependencies failed"
}

InstallBeGateway() {
	Info "Install BeGateway..."

	CURRDIR=$(pwd)
	DESTDIR=/usr/local/mgr5/src/begateway

	mkdir -p ${DESTDIR} && cp -rfa "${CURRDIR}"/* ${DESTDIR}/
	cd ${DESTDIR} && make install || ExitError "Install BeGateway failed"
	cd ${CURRDIR}

	Info "Install successfuly finished"
}

InstallDeps
InstallBeGateway
