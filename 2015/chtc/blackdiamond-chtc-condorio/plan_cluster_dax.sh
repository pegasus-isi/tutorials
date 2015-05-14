#!/bin/sh

set -e

# plan and submit the  workflow
pegasus-plan \
    --conf pegasus.conf \
    --sites chtc \
    --output-site local \
    --dir work/dags \
    --dax diamond.dax \
    -v \
    --force \
    --cleanup none \
    --cluster label \
    --submit
