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
SPLIT_DIR=${TOPDIR}/examples/split
# create the transformation catalog (tc)
echo
echo "Creating the transformation catalog..."

cat >tc.txt <<EOF
# This is the transformation catalog. It lists information about each of the
# executables that are used by the workflow.

EOF

#for diamond example
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

#all other executables
SITE=condorpool
cat >>tc.txt <<EOF
tr ls { 
  site ${SITE} {
    pfn "/bin/ls"
    arch "x86_64"
    os "linux"
    type "INSTALLED"
  }
}

tr cat { 
  site ${SITE} {
    pfn "/bin/cat"
    arch "x86_64"
    os "linux"
    type "INSTALLED"
  }
}

tr curl { 
  site ${SITE} {  
    pfn "/usr/bin/curl"
    arch "x86_64"
    os "linux"
    type "INSTALLED"
  }
}

tr wc { 
  site ${SITE} { 
    pfn "/usr/bin/wc"
    arch "x86_64"
    os "linux"
    type "INSTALLED"
  }
}

tr split { 
   site ${SITE} { 
    pfn "/usr/bin/split"
    arch "x86_64"
    os "linux"
    type "INSTALLED"
  }
}

EOF


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
pegasus.html    file://${SPLIT_DIR}/input/pegasus.html    site="local"
EOF


# generate the pegasusrc file

cat >${HOME}/.pegasusrc<<EOF
# This tells Pegasus where to find the Site Catalog
pegasus.catalog.site.file=sites.xml

# This tells Pegasus where to find the Replica Catalog
pegasus.catalog.replica=File
pegasus.catalog.replica.file=rc.dat

# This tells Pegasus where to find the Transformation Catalog
pegasus.catalog.transformation=Text
pegasus.catalog.transformation.file=tc.txt

# This is the name of the application for analytics
pegasus.metrics.app=pegasus-tutorial

# create one cleanup job per level of the workflow
pegasus.file.cleanup.clusters.num = 1


# data configuration for pegasus
pegasus.data.configuration=condorio

EOF

examples=( diamond   merge  pipeline  process  split)
for EXAMPLE in "${examples[@]}"; do
    cp ${HOME}/.pegasusrc ${EXAMPLE}/
    cp sites.xml ${EXAMPLE}/
    cp tc.txt ${EXAMPLE}/
done

cp rc.dat ./diamond/
cp rc.dat ./split

rm sites.xml tc.txt rc.dat