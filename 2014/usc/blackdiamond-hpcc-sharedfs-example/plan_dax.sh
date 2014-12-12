#!/bin/sh

set -e

# plan and submit the  workflow
pegasus-plan \
    --conf pegasus.conf \
    --sites hpcc \
    --output-site local \
    --dir work/dags \
    --dax diamond.dax \
    -v \
    --force \
    --nocleanup \

