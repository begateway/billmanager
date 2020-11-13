help:
				@echo "make module"
				@echo " - generates a module archive"
				@echo "make install"
				@echo '  - installs module to Billmanager'

module:
				tar --exclude='*.DS_Store*' -zcvf billmanager_begateway.tar.gz include paymethod Makefile

install:
				cp -a include ${CORE_DIR}
				cp -a paymethod/begateway/* ${CORE_DIR}
				chmod 777 ${CORE_DIR}/cgi/begatewaypayment.php
				chmod 777 ${CORE_DIR}/cgi/begatewaypayurl.php
				chmod 777 ${CORE_DIR}/paymethods/pmbegateway
				killall core
