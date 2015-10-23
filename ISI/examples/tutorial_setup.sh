#!/bin/bash 

set -e 

for count in `seq -w  1 30`; do
    user=pegtrain$count

    echo "------------------------------"
    echo "setting up for user $user"

#    set the password
#    echo "setting password for user $user"
#    echo "XXX" | passwd --stdin $user

    
    su - $user <<EOF

	cd ~${user}/
        pwd

	set +e
	condor_rm $user
	set -e

        # remove the output and scratch dir
        rm -rf ./run ./outputs ./examples

	cp -r /local-scratch/vahi/software/git/tutorials/ISI/examples .

	cd ./examples
	./generate_catalogs.sh
        rm ./*~ ./tutorial_setup.sh
	cd ..

        
	
	#setup workflow db for dashboard
	rm -f ~${user}/.pegasus/workflow.db
	pegasus-db-admin create
	chmod +r ~${user}/.pegasus/workflow.db
        chmod +rx ~${user}
	chmod +rx ~${user}/.pegasus

EOF
     
done