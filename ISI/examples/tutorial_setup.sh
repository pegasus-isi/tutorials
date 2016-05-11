#!/bin/bash 

set -e 

for count in `seq -w  01 03`; do
    user=pegtrain$count

    echo "------------------------------"
    echo "setting up for user $user"

#    set the password
#    passwd="XXX"
#    echo "setting password for user $user to $passwd"    
#    echo $passwd | passwd --stdin $user

    
    su - $user <<EOF

	cd ~${user}/
        pwd

	set +e
	condor_rm $user
	set -e

        # setup SSH key to be used by tutorial
        rm -rf ~/.ssh
        mkdir -p ~/.ssh
        ssh-keygen -b 2048 -t rsa -f ~/.ssh/workflow -N ""
        cat ~/.ssh/workflow.pub > ~/.ssh/authorized_keys2

	#setup workflow db for dashboard
	rm -f ~${user}/.pegasus/workflow.db
	pegasus-db-admin create
	chmod +r ~${user}/.pegasus/workflow.db
        chmod +rx ~${user}
	chmod +rx ~${user}/.pegasus

        rm -rf ~/examples ~/run ~/tutorial
        mkdir -p ~/tutorial

EOF
     
done
