help:
				@echo "make module"
				@echo " - generates a module archive"
				@echo "make install"
				@echo '  - installs module to Billmanager'

module:
				tar --exclude='*.DS_Store*' -zcvf billmanager_begateway.tar.gz include paymethod Makefile 

install:
				cp -a include /usr/local/mgr5/
				cp -a paymethod/begateway/* /usr/local/mgr5/
				chmod 777 /usr/local/mgr5/cgi/begatewaypayment.php
				chmod 777 /usr/local/mgr5/cgi/begatewaypayurl.php
				chmod 777 /usr/local/mgr5/paymethods/pmbegateway.php
				killall core
