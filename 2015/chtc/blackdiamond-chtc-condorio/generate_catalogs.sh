#!/bin/bash

set -e

TOPDIR=`pwd`

# pegasus bin directory is needed to find keg
BIN_DIR=`pegasus-config --bin`

JOB_CLUSTERS_SIZE=2
LOCAL_PBS_PEGASUS_HOME=`dirname $BIN_DIR`

mkdir -p conf

# create the site catalog
echo "Creating the site catalog..."
cat >conf/sites.xml <<EOF
<?xml version="1.0" encoding="UTF-8"?>
<sitecatalog xmlns="http://pegasus.isi.edu/schema/sitecatalog" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://pegasus.isi.edu/schema/sitecatalog http://pegasus.isi.edu/schema/sc-4.0.xsd" version="4.0">
    
    <!-- The local site contains information about the submit host -->
    <!-- The arch and os keywords are used to match binaries in the transformation catalog -->
    <site  handle="local" arch="x86_64" os="LINUX" >
        <directory  path="${TOPDIR}/data/outputs" type="shared-storage" free-size="" total-size="">
         <file-server  operation="all" url="file://${TOPDIR}/data/outputs"/>
       </directory>
       <directory  path="${TOPDIR}/data/scratch" type="shared-scratch" free-size="" total-size="">
         <file-server  operation="all" url="file://${TOPDIR}/data/scratch"/>
         </directory>
    </site>

    <!-- the chtc condor pool -->
    <site handle="chtc" arch="x86_64" os="LINUX" osrelease="" osversion="" glibc="">
         <profile namespace="condor" key="+ProjectName" >"con-train"</profile>
         <profile namespace="condor" key="universe" >vanilla</profile>
         <profile namespace="pegasus" key="style" >condor</profile>
    </site>


    <!-- the hpcc cluster designates the USC HPCC cluster
         It is representative of a typical HPC cluster with a 
         shared fliesystem setup. -->
    <site  handle="hpcc" arch="x86_64" os="LINUX">        
        <!--shared scratch directory indicates a directory that is visible
            on all the nodes of the HPCC cluster. This is where the jobs
            execute -->
        <directory type="shared-scratch" path="$TOPDIR/HPCC/shared-scratch">
            <file-server operation="all" url="file://$TOPDIR/HPCC/shared-scratch"/>
        </directory>

        <!-- tell pegasus it is a PBS cluster and submission to be via glite -->
        <profile namespace="pegasus" key="style" >glite</profile>
        <profile namespace="condor" key="grid_resource">pbs</profile>

        <profile namespace="env" key="PEGASUS_HOME">$LOCAL_PBS_PEGASUS_HOME</profile>
        <profile namespace="pegasus" key="change.dir">true</profile>

        <!-- maxwalltime in minutes for the jobs run on this cluster -->
        <profile namespace="globus" key="maxwalltime">60</profile>
    </site>

</sitecatalog>
EOF

# create the transformation catalog (tc)
echo
echo "Creating the transformation catalog..."

cat >conf/tc.dat <<EOF
# This is the transformation catalog. It lists information about each of the
# executables that are used by the workflow.

EOF

transformations=( preprocess findrange analyze)
for TRANSFORMATION in "${transformations[@]}"; do 
    cat >>conf/tc.dat <<EOF
tr pegasus::$TRANSFORMATION:4.0 {
    site local {
        pfn "/usr/bin/pegasus-keg"
        arch "x86_64"
        os "linux"
        type "STAGEABLE"
        profile pegasus "clusters.size" "$JOB_CLUSTERS_SIZE" 
    }
}
EOF
done



# create the replica catalog
echo
echo "Creating the Replica Catalog..."

# generate the input file
mkdir -p input
echo "This is sample input to KEG" >input/f.a

cat >conf/rc.dat<<EOF
# This is the replica catalog. It lists information about each of the
# input files used by the workflow.

# The format is:
# LFN     PFN    site="SITE"

f.a    file://$TOPDIR/input/f.a    site="local"
EOF