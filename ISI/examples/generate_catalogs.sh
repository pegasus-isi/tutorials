#!/bin/bash

set -e

TOPDIR=$HOME

# pegasus bin directory is needed to find keg
BIN_DIR=`pegasus-config --bin`

JOB_CLUSTERS_SIZE=2

# create the site catalog
echo "Creating the site catalog..."
cat >sites.xml <<EOF
<?xml version="1.0" encoding="UTF-8"?>
<sitecatalog xmlns="http://pegasus.isi.edu/schema/sitecatalog" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://pegasus.isi.edu/schema/sitecatalog http://pegasus.isi.edu/schema/sc-4.0.xsd" version="4.0">
    
    <!-- The local site contains information about the submit host -->
    <!-- The arch and os keywords are used to match binaries in the transformation catalog -->
    <site  handle="local" arch="x86_64" os="LINUX" >
        <directory  path="${TOPDIR}/outputs" type="shared-storage" free-size="" total-size="">
         <file-server  operation="all" url="file://${TOPDIR}/outputs"/>
       </directory>
       <directory  path="${TOPDIR}/run" type="shared-scratch" free-size="" total-size="">
         <file-server  operation="all" url="file://${TOPDIR}/run"/>
         </directory>
    </site>

    <!-- the  condor pool on which compute jobs are run -->
    <site handle="condorpool" arch="x86_64" os="LINUX" osrelease="" osversion="" glibc="">
         <!-- the project name for the training accounts on OSG -->
         <profile namespace="condor" key="+ProjectName" >"PegasusTraining"</profile>
         <profile namespace="condor" key="requirements">OSGVO_OS_STRING == "RHEL 6" &amp;&amp; Arch == "X86_64"</profile>

         <profile namespace="condor" key="universe" >vanilla</profile>
         <profile namespace="pegasus" key="style" >condor</profile>
    </site>

</sitecatalog>
EOF

DIAMOND_DIR=${TOPDIR}/examples/diamond
# create the transformation catalog (tc)
echo
echo "Creating the transformation catalog..."

cat >${DIAMOND_DIR}/tc.txt <<EOF
# This is the transformation catalog. It lists information about each of the
# executables that are used by the workflow.

EOF

transformations=( preprocess findrange analyze)
for TRANSFORMATION in "${transformations[@]}"; do 
    cat >>tc.txt <<EOF
tr $TRANSFORMATION {
    site local {
        pfn "${DIAMOND_DIR}/bin/transformation.py"
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
mkdir -p ${DIAMOND_DIR}/input
echo "This is sample input to KEG" >${DIAMOND_DIR}/input/f.a

cat >rc.dat<<EOF
# This is the replica catalog. It lists information about each of the
# input files used by the workflow.

# The format is:
# LFN     PFN    site="SITE"

f.a    file://${DIAMOND_DIR}/input/f.a    site="local"
EOF