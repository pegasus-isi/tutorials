#!/bin/bash

if [ $# -ne 1 ]; then
	echo "Usage: $0 DAXFILE"
	exit 1
fi

DAXFILE=$1

# This command tells Pegasus to plan the workflow contained in 
# "diamond.dax" using the config file "pegasus.conf". The planned
# workflow will be stored in a relative directory named "submit".
# The execution site is "PegasusVM" and the output site is "local".
# --input-dir tells Pegasus to pick up inputs for the workflow from that directory
# --output-dir tells Pegasus to place the outputs in that directory
pegasus-plan  --dax $DAXFILE --input-dir ./input --output-dir ./outputs \
             --sites condorpool --submit
