#!/bin/bash

set -e

#############################################################################
#

# XSEDE alloaction to charge the jobs to
ALLOCATION="TG-STA110014S"

# Your username at SDSC. If you do not know what it is, run:
#      gsissh gordon.sdsc.xsede.org whoami
#SDSC_USERNAME="ux454281"
SDSC_USERNAME=`gsissh gordon.sdsc.xsede.org whoami`

#############################################################################

TOP_DIR=`pwd`

cat >sites.xml <<EOF
<?xml version="1.0" encoding="UTF-8"?>
<sitecatalog xmlns="http://pegasus.isi.edu/schema/sitecatalog" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://pegasus.isi.edu/schema/sitecatalog http://pegasus.isi.edu/schema/sc-4.0.xsd" version="4.0">

    <site  handle="local" arch="x86" os="LINUX">
        <!-- the base directory where workflow jobs will execute for local site -->
        <directory type="shared-scratch" path="$TOP_DIR/work/scratch">
            <file-server operation="all" url="file://$TOP_DIR/work/scratch"/>
        </directory>

        <!-- the directory where outputs will be placed  -->
        <directory type="local-storage" path="$TOP_DIR/outputs">
            <file-server operation="all" url="file://$TOP_DIR/outputs"/>
        </directory>
    </site>

    <site  handle="sdsc-gordon" arch="x86_64" os="LINUX">
        <grid  type="gt5" contact="gordon-ln4.sdsc.xsede.org:2119/jobmanager-fork" scheduler="Fork" jobtype="auxillary"/>
        <grid  type="gt5" contact="gordon-ln4.sdsc.xsede.org:2119/jobmanager-pbs" scheduler="unknown" jobtype="compute"/>

        <directory type="shared-scratch" path="/oasis/scratch/$SDSC_USERNAME/temp_project">
            <file-server operation="all" url="gsiftp://oasis-dm.sdsc.xsede.org:2811/oasis/scratch/$SDSC_USERNAME/temp_project"/>
        </directory>

        <profile namespace="globus" key="project">$ALLOCATION</profile>
        <profile namespace="env" key="PEGASUS_HOME">/home/ux454281/software/pegasus/pegasus-4.5.0</profile>
    </site>

</sitecatalog>
EOF



