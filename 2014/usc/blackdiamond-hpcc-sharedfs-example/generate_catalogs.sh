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
    <site  handle="local" arch="x86_64" os="LINUX">
        <directory type="shared-scratch" path="$TOPDIR/work">
            <file-server operation="all" url="file://$TOPDIR/work"/>
        </directory>
        <directory type="local-storage" path="$TOPDIR/outputs">
            <file-server operation="all" url="file://$TOPDIR/outputs"/>
        </directory>
    </site>

    <!-- the hpcc cluster designates the USC HPCC cluster -->
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
        <profile namespace="globus" key="maxwalltime">600</profile>
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
    site hpcc {
        pfn "/usr/bin/pegasus-keg"
        arch "x86_64"
        os "linux"
        type "INSTALLED"
        profile pegasus "clusters.size" "$JOB_CLUSTERS_SIZE" 
    }
}
EOF
done

cat >>conf/tc.dat<<EOF
# pegasus mpi clustering executable
tr pegasus::mpiexec{
    site hpcc {
        pfn "/home/rcf-proj/gmj/pegasus/SOFTWARE/pegasus/pegasus-mpi-cluster-wrapper"
        arch "x86_64"
        os "linux"
        type "INSTALLED"
        profile pegasus "clusters.size" "$JOB_CLUSTERS_SIZE" 

        #the various parameters to specify the size of the MPI job
        #in which the workflow runs
        profile globus "jobtype" "mpi"
        profile globus "maxwalltime" "2880"
        # specfiy the ppn parameter.
        profile globus "xcount" "4:IB"
        # specify the nodes parameter
        profile globus "hostcount" "1"
        #specify the pmem parameter
        profile globus "maxmemory" "1gb"
        #specify the mem parameter
        profile globus "totalmemory" "16gb"

    }
}
EOF


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
# LFN     PFN    pool="SITE"

f.a    file://$TOPDIR/input/f.a    pool="hpcc"
EOF